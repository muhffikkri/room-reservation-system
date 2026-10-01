<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Volume jejak audit laporan dan perubahan status fasilitas (BR-10, BR-11).
 *
 * Sibling dari ReportVolumeSeeder. Semua laporan sudah ada di status `baru`
 * ketika seeder ini jalan; di sini setiap laporan ditindaklanjuti dengan
 * ReportService::transition() supaya tiap langkah menulis tepat satu baris
 * report_updates, sama seperti yang dilakukan form petugas.
 *
 * PERLAKUAN 72 LAPORAN
 *
 * Dua belas laporan pertama (`index % 12` sama dengan 0 atau 1) dibiarkan
 * di status `baru` tanpa disentuh. Jadi filter status `baru` punya isi, dan
 * ada laporan tanpa satu pun jejak transisi. Enam puluh laporan sisanya
 * selalu diproses lebih dulu.
 *
 * Setelah itu, laporan dibagi lima perlakuan. Penentuannya bukan nomor urut
 * laporan melainkan status fasilitasnya, karena
 * FacilityLifecycle::markForRepair hanya menerima fasilitas yang masih
 * `aktif`:
 *
 *   diproses        `baru` -> `diproses`, dibiarkan terbuka. Fasilitas tidak
 *                  pernah ditandai perbaikan, jadi tidak ada pemulatan.
 *   perbaikan      `baru` -> `diproses`, lalu ditandai perbaikan. Laporan
 *                  tetap terbuka dan fasilitasnya berakhir `perbaikan`,
 *                  sehingga status perbaikan pada katalog berasal dari
 *                  laporan dan bukan dari nilai yang ditulis bebas.
 *   perbaikan-selesai
 *                  ditandai perbaikan, ditutup `selesai`, lalu fasilitasnya
 *                  dikembalikan aktif. Ini jalur lengkap perbaikan ->
 *                  selesai -> aktif.
 *   selesai        `baru` -> `diproses` -> `selesai`.
 *   ditolak        `baru` -> `diproses` -> `ditolak`.
 *
 * Jejak audit yang dihasilkan oleh seeder ini: 60 baris transisi ke
 * `diproses`, ditambah 36 baris transisi penutup (24 ke `selesai` dan 12 ke
 * `ditolak`), ditambah 4 baris legacy dengan old_status kosong.
 *
 * BARIS LEGACY
 *
 * Empat laporan kelompok `baru` mendapat satu baris ReportUpdate dengan
 * old_status kosong, yaitu baris saat laporan lahir yang direkam belakangan.
 * Status laporan tetap `baru`, jadi jejaknya tetap konsisten: satu-satunya
 * peristiwa yang tercatat adalah lahir sebagai `baru`.
 *
 * PENANGANAN WAKTU
 *
 * Laporan riwayat dibuat 1 sampai 35 hari lalu, sedangkan `transition()`
 * mengisi handled_at dan created_at baris audit dengan now(). Tanpa
 * penyesuaian, halaman detail akan menampilkan laporan berumur sebulan
 * yang baru saja diproses. Karena itu setiap langkah pada laporan riwayat
 * dimundurkan ke waktu yang masuk akal, selalu sesudah waktu lahir laporan
 * dan selalu bertambah antar langkah.
 */
final class ReportUpdateVolumeSeeder extends Seeder
{
    /**
     * Laporan diproses lalu dibiarkan terbuka, tanpa pernah ditandai perbaikan.
     */
    private const PROCESSED_OPEN = 'diproses';

    /**
     * Laporan diproses dan ditandai perbaikan, lalu dibiarkan terbuka.
     */
    private const MARKED_OPEN = 'perbaikan';

    /**
     * Laporan diproses lalu ditutup selesai.
     */
    private const COMPLETED = 'selesai';

    /**
     * Laporan diproses lalu ditutup ditolak.
     */
    private const REJECTED = 'ditolak';

    /**
     * Laporan diproses, ditandai perbaikan, ditutup selesai, lalu fasilitasnya
     * dikembalikan aktif.
     */
    private const MARKED_COMPLETED = 'perbaikan-selesai';

    /**
     * Jumlah laporan yang mendapat baris legacy creation.
     */
    private const LEGACY_ROWS = 4;

    /**
     * Jumlah laporan yang dibiarkan terbuka dan fasilitasnya ditandai perbaikan.
     */
    private const MARKED_OPEN_COUNT = 12;

    /**
     * Jumlah laporan yang ditandai perbaikan, ditutup, lalu difasilitasnya dipulihkan.
     */
    private const MARKED_COMPLETED_COUNT = 12;

    /**
     * Jumlah laporan untuk tiap status penutup (selesai dan ditolak).
     */
    private const CLOSING_COUNT = 12;

    public function run(): void
    {
        $service = app(ReportService::class);
        $officers = VolumeRoster::petugas();

        if ($officers->isEmpty()) {
            throw new RuntimeException('Tidak ada petugas aktif; seeder verifikasi akun harus dijalankan lebih dulu.');
        }

        $reports = $this->volumeReports();
        $reports = $this->volumeReports();

        if ($reports->count() !== ReportVolumeSeeder::TOTAL) {
            throw new RuntimeException(
                "Laporan volume berjumlah {$reports->count()}, seharusnya ".ReportVolumeSeeder::TOTAL.'. Jalankan ReportVolumeSeeder lebih dulu.'
            );
        }

        // Dua dari setiap dua belas laporan dibiarkan di status `baru`.
        // Pisahannya lewat indeks, jadi collection harus di-loop langsung:
        // filter() hanya menerima nilai, bukan kuncinya.
        $untouched = collect();
        $processed = collect();

        foreach ($reports->values() as $index => $report) {
            if ($index % 12 === 0 || $index % 12 === 1) {
                $untouched->push($report);
            } else {
                $processed->push($report);
            }
        }

        $plans = $this->assignPlans($processed);
        $position = 0;

        foreach ($processed->values() as $report) {
            $plan = $plans[$report->id];
            $this->walk($service, $report, $plan, $officers, $position);
            $position++;
        }

        $this->legacyCreationRows($untouched->values()->all(), $officers);
        $this->assertCoverage();
    }

    /**
     * Bagi laporan yang akan diproses ke lima perlakuan.
     *
     * Marking perbaikan hanya boleh dipakai pada fasilitas yang masih
     * `aktif`, karena FacilityLifecycle::markForRepair menolak yang lain. Jadi
     * urutanakyanya diambil dari laporan yang fasilitasnya masih aktif, bukan
     * dari nomor urut laporan, supaya tidak ada langkah yang ditolak halfway
     * dan tidak ada jumlah yang bergeser.
     *
     * Sebelas dari setiap dua belas laporan yang tersisa tetap `diproses`.
     *
     * @param  Collection<int, Report>  $processed
     * @return array<int, string>
     */
    private function assignPlans(Collection $processed): array
    {
        $markable = $processed
            ->filter(fn (Report $report) => $report->facility->status === 'aktif')
            ->values();

        $needed = self::MARKED_OPEN_COUNT + self::MARKED_COMPLETED_COUNT;

        if ($markable->count() < $needed) {
            throw new RuntimeException(
                "Hanya {$markable->count()} laporan pada fasilitas aktif, dibutuhkan {$needed} untuk skenario perbaikan.",
            );
        }

        $plans = [];

        foreach ($markable->take(self::MARKED_OPEN_COUNT) as $report) {
            $plans[$report->id] = self::MARKED_OPEN;
        }

        foreach ($markable->slice(self::MARKED_OPEN_COUNT, self::MARKED_COMPLETED_COUNT) as $report) {
            $plans[$report->id] = self::MARKED_COMPLETED;
        }

        $taken = array_keys($plans);
        $rest = $processed->reject(fn (Report $report) => in_array($report->id, $taken, true))->values();

        foreach ($rest->take(self::CLOSING_COUNT) as $report) {
            $plans[$report->id] = self::COMPLETED;
        }

        foreach ($rest->slice(self::CLOSING_COUNT, self::CLOSING_COUNT) as $report) {
            $plans[$report->id] = self::REJECTED;
        }

        // Sisanya diproses lalu dibiarkan terbuka, termasuk laporan pada
        // fasilitas nonaktif yang memang tidak bisa ditandai perbaikan.
        foreach ($rest->slice(self::CLOSING_COUNT * 2) as $report) {
            $plans[$report->id] = self::PROCESSED_OPEN;
        }

        return $plans;
    }

    /**
     * Jalankan seluruh langkah satu laporan sesuai perlakuannya.
     *
     * @param  Collection<int, User>  $officers
     */
    private function walk(
        ReportService $service,
        Report $report,
        string $plan,
        Collection $officers,
        int $position,
    ): void {
        $officer = $officers[$position % $officers->count()];

        $this->toDiproses($service, $report, $officer);

        if ($plan === self::PROCESSED_OPEN) {
            return;
        }

        $marked = $plan === self::MARKED_OPEN || $plan === self::MARKED_COMPLETED;

        if ($marked) {
            $service->markFacilityForRepair($report, $officer);
            $this->rewindFacility($report, 2);
        }

        if ($plan === self::MARKED_OPEN) {
            return;
        }

        // Petugas penutup boleh berbeda dari petugas pembuka supaya satu
        // laporan tidak selalu ditangani orang yang sama.
        $closer = $officers[($position + 1) % $officers->count()];
        $closing = $plan === self::REJECTED ? 'ditolak' : 'selesai';

        $this->transition($service, $report, $closer, $closing);

        if ($plan === self::MARKED_COMPLETED) {
            $service->restoreFacilityToActive($report, $closer);
            $this->rewindFacility($report, 4);
        }
    }

    /**
     * Langkah pertama untuk semua laporan yang bukan `baru`.
     */
    private function toDiproses(ReportService $service, Report $report, User $officer): void
    {
        $this->transition($service, $report, $officer, 'diproses');
    }

    /**
     * Satu transisi yang sah menurut ReportService::TRANSITIONS, dengan
     * catatan yang memenuhi syarat panjang minimum BR-10, lalu waktu
     * penanganannya dikembalikan ke kronologi laporan.
     */
    private function transition(ReportService $service, Report $report, User $officer, string $to): void
    {
        $service->transition($report, $officer, $to, $this->note($report, $to));

        $bornAt = $report->created_at;
        $handledAt = $this->stepTime($report, $report->updates()->count());

        $report->handled_at = $handledAt;
        $report->saveQuietly();

        $step = $report->updates()->orderByDesc('id')->first();

        if ($step !== null) {
            $step->created_at = $handledAt;
            $step->updated_at = $handledAt;
            $step->saveQuietly();
        }
    }

    /**
     * Waktu satu langkah untuk laporan riwayat: sesudah waktu lahir, dan
     * bertambahylebih lama pada setiap langkah. Laporan hari ini dibiarkan
     * memakai now() dari service karena memang baru diproses.
     */
    private function stepTime(Report $report, int $step): Carbon
    {
        $bornAt = $report->created_at ?? now();

        if ($bornAt->isToday()) {
            return now();
        }

        return $bornAt->copy()
            ->addHours(2 + $step * 3)
            ->addMinutes($report->id % 60);
    }

    /**
     * Fasilitas yang ditandai perbaikan atau dipulatkan juga menerima
     * updated_at sendiri. Tanpa itu, daftar admin akan-show facilities
     * yang "diperbaiki" berdasarkan waktu sekarang sementara laporannya
     * berumur sebulan.
     */
    private function rewindFacility(Report $report, int $step): void
    {
        $facility = $report->facility()->first();
        $bornAt = $report->created_at ?? now();

        if ($facility === null || $bornAt->isToday()) {
            return;
        }

        $facility->updated_at = $bornAt->copy()
            ->addHours(2 + $step * 3)
            ->addMinutes($report->id % 60);
        $facility->saveQuietly();
    }

    /**
     * Catatan transisi. Penutup wajib minimal sepuluh karakter dan memakai
     * nomor laporan supaya mudah dibaca di halaman detail.
     */
    private function note(Report $report, string $status): string
    {
        return match ($status) {
            'diproses' => "Laporan #{$report->id} sedang ditangani petugas.",
            'selesai' => "Perbaikan laporan #{$report->id} selesai dan fasilitas bisa dipakai.",
            'ditolak' => "Laporan #{$report->id} ditolak setelah pemeriksaan petugas.",
            default => "Catatan laporan #{$report->id}.",
        };
    }

    /**
     * Semua laporan milik pengguna volume, urut id. Laporan demo milik
     * budi@student.kampus.test sehingga tidak ikut tersentuh.
     *
     * @return Collection<int, Report>
     */
    private function volumeReports(): Collection
    {
        return Report::query()
            ->whereIn('user_id', VolumeRoster::pengguna()->pluck('id'))
            ->orderBy('id')
            ->get();
    }

    /**
     * Baris audit creation untuk laporan yang lahir sebelum audit ada:
     * old_status kosong dan waktu yang sama dengan waktu lahir laporan.
     *
     * @param  list<Report>  $untouched
     * @param  Collection<int, User>  $officers
     */
    private function legacyCreationRows(array $untouched, Collection $officers): void
    {
        foreach (array_slice($untouched, 0, self::LEGACY_ROWS) as $index => $report) {
            $officer = $officers[$index % $officers->count()];
            $bornAt = $report->created_at ?? now();

            $row = ReportUpdate::create([
                'report_id' => $report->id,
                'user_id' => $officer->id,
                'old_status' => null,
                'new_status' => $report->status,
                'note' => "Laporan #{$report->id} tercatat masuk pada hari yang sama.",
            ]);

            $row->created_at = $bornAt;
            $row->updated_at = $bornAt;
            $row->saveQuietly();
        }
    }

    /**
     * Bukti cakupan, supaya masalah ketahuan saat seed dan bukan saat demo.
     */
    private function assertCoverage(): void
    {
        $reportIds = VolumeRoster::pengguna()->pluck('id');

        foreach (['baru', 'diproses', 'selesai', 'ditolak'] as $status) {
            if (Report::whereIn('user_id', $reportIds)->where('status', $status)->doesntExist()) {
                throw new RuntimeException("Tidak ada laporan volume berstatus {$status}.");
            }
        }

        foreach (array_keys(Report::CATEGORIES) as $category) {
            if (Report::whereIn('user_id', $reportIds)->where('category', $category)->doesntExist()) {
                throw new RuntimeException("Tidak ada laporan volume untuk kategori {$category}.");
            }
        }

        // Nilai status fasilitas masih berupa literal di seluruh codebase
        // (belum ada konstanta owning), jadi di sini juga literal.
        foreach (['aktif', 'nonaktif', 'perbaikan'] as $status) {
            if (Facility::where('name', 'like', VolumeRoster::FACILITY_PREFIX.'%')->where('status', $status)->doesntExist()) {
                throw new RuntimeException("Tidak ada fasilitas volume berstatus {$status}.");
            }
        }

        if (ReportUpdate::whereNull('old_status')->count() < self::LEGACY_ROWS) {
            throw new RuntimeException('Baris audit legacy dengan old_status kosong tidak lengkap.');
        }

        // Setiap laporan yang ditutup harus punya resolution_note, karena
        // BR-10 mewajibkan catatan penutup.
        $closedWithoutNote = Report::whereIn('user_id', $reportIds)
            ->whereIn('status', ReportService::CLOSING_STATUSES)
            ->where(function ($query): void {
                $query->whereNull('resolution_note')->orWhere('resolution_note', '');
            })
            ->count();

        if ($closedWithoutNote > 0) {
            throw new RuntimeException("Ada {$closedWithoutNote} laporan tertutup tanpa catatan resolusi.");
        }
    }
}
