<?php

namespace Database\Seeders;

use App\Models\AccountVerificationAction;
use App\Models\User;
use App\Services\AccountVerificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Volume audit verifikasi akun (BR-14).
 *
 * Seeder ini tidak menulis kolom account_status maupun baris audit secara
 * langsung. Semua perpindahan status lewat AccountVerificationService, yang
 * juga menulis tepat satu baris account_verification_actions per tindakan.
 * Dengan begitu data volume tidak mungkin berbeda dari data yang dihasilkan
 * form admin, termasuk aturan yang hanya ada di service: restore()
 * membersihkan rejected_by dan rejected_at.
 *
 * CAKUPAN
 *
 * Hanya kelompok role `pengguna` yang bisa jadi sasaran. Service menolak
 * target lain dengan NotFoundHttpException demi menyembunyikan keberadaan
 * akun non-pengguna, jadi admin dan petugas memang tidak punya jalur
 * verifikasi dan tidak punya jejak audit.
 *
 *  - verified: dua puluh empat akun pengguna volume.
 *  - rejected: enam akun ditolak; dua di antaranya ditolak lagi setelah
 *    dipulihkan, sehingga satu sasaran punya tiga baris dari tiga aksi dan
 *    tiga aktor berbeda.
 *  - restored: empat akun ditolak lalu dipulihkan, dan berakhir pending
 *    dengan jejak penolakan yang sudah dikosongkan oleh service.
 *  - pending: tiga puluh akun tidak pernah disentuh, sehingga antrean
 *    verifikasi (15 baris per halaman) punya lebih dari satu halaman.
 */
final class AccountVerificationActionVolumeSeeder extends Seeder
{
    /**
     * Email admin demo, satu-satunya admin yang sudah aktif sebelum seeder
     * volume berjalan.
     */
    private const DEMO_ADMIN_EMAIL = 'admin@kampus.test';

    /**
     * Jumlah akun ditolak yang hanya ditolak sekali. Sisanya ditolak,
     * dipulihkan, lalu ditolak lagi.
     */
    private const SINGLE_REJECTION_COUNT = 4;

    public function run(): void
    {
        $service = app(AccountVerificationService::class);
        $actors = $this->prepareActors();

        foreach (UserVolumeSeeder::GROUPS as $group => $spec) {
            // AccountVerificationService hanya menerima target role 'pengguna'
            // (AccountVerificationService::verify). Kelompok petugas dan admin
            // sudah aktif sejak lahir dan memang tidak punya jejak audit
            // verifikasi, jadi keduanya dilewati di sini.
            if ($spec['role'] !== 'pengguna') {
                continue;
            }

            $targets = VolumeRoster::group($group);

            if ($targets->count() !== $spec['count']) {
                throw new RuntimeException(
                    "Kelompok akun {$group} berisi {$targets->count()} baris, seharusnya {$spec['count']}. Jalankan UserVolumeSeeder lebih dulu."
                );
            }

            foreach ($targets->values() as $position => $target) {
                match ($group) {
                    'ditolak' => $this->rejectTarget($service, $target, $position, $actors),
                    'restore' => $this->restoreTarget($service, $target, $position, $actors),
                    // pending dibiarkan apa adanya: itu bahan antrean verifikasi.
                    'pending' => null,
                    default => $service->verify($target, $actors[$position % $actors->count()]),
                };
            }
        }

        $this->assertCoverage();
    }

    /**
     * Daftar admin yang boleh jadi aktor audit.
     *
     * Admin volume sudah `aktif` sejak UserVolumeSeeder jalan, jadi tidak ada
     * tahap verifikasi untuk admin di sini. Yang perlu dipastikan hanya
     * bahwa admin demo ada, karena dia jadi aktor pertama pada baris audit
     * volume.
     *
     * @return Collection<int, User>
     */
    private function prepareActors(): Collection
    {
        $demoAdmin = User::where('email', self::DEMO_ADMIN_EMAIL)->first();

        if (! $demoAdmin instanceof User || ! $demoAdmin->isActive()) {
            throw new RuntimeException(
                'Akun admin demo harus sudah dibuat oleh UserSeeder sebelum seeder verifikasi.'
            );
        }

        return collect([$demoAdmin])->merge(VolumeRoster::admin());
    }

    /**
     * @param  Collection<int, User>  $actors
     */
    private function rejectTarget(AccountVerificationService $service, User $target, int $position, Collection $actors): void
    {
        $service->reject($target, $actors[$position % $actors->count()]);

        if ($position < self::SINGLE_REJECTION_COUNT) {
            return;
        }

        $service->restore($target, $actors[$position % $actors->count()]);
        $service->reject($target, $actors[($position + 1) % $actors->count()]);
    }

    /**
     * @param  Collection<int, User>  $actors
     */
    private function restoreTarget(AccountVerificationService $service, User $target, int $position, Collection $actors): void
    {
        $service->reject($target, $actors[$position % $actors->count()]);
        $service->restore($target, $actors[($position + 1) % $actors->count()]);
    }

    /**
     * Bukti cakupan. Disebut eksplisit supaya seeder gagal cepat ketika
     * orkestrasi di atas berubah dan suatu nilai ikut hilang, alih-alih
     * menyisakan tabel audit yang hanya terisi separuh.
     */
    private function assertCoverage(): void
    {
        $actions = [
            AccountVerificationAction::VERIFIED,
            AccountVerificationAction::REJECTED,
            AccountVerificationAction::RESTORED,
        ];

        foreach ($actions as $action) {
            if (AccountVerificationAction::where('action', $action)->doesntExist()) {
                throw new RuntimeException("Tidak ada baris audit dengan aksi {$action}; orkestrasi seeder tidak lengkap.");
            }
        }

        foreach (['aktif', 'pending', 'ditolak'] as $status) {
            if (User::where('account_status', $status)->doesntExist()) {
                throw new RuntimeException("Tidak ada akun berstatus {$status}; orkestrasi seeder tidak lengkap.");
            }
        }

        // Antrean verifikasi admin memakai scope pendingPengguna pada 15 baris
        // per halaman. Dengan 16 baris atau lebih, tautan halaman kedua akan
        // benar-benar muncul.
        $queue = User::pendingPengguna()->count();

        if ($queue < 16) {
            throw new RuntimeException("Antrean verifikasi hanya berisi {$queue} akun; pagination tidak akan teruji.");
        }
    }
}
