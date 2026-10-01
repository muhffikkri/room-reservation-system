<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Notifications\ReservationOverlapRejected;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Volume notifikasi in-app.
 *
 * SEEDER INI TIDAK MEMBUAT NOTIFIKASI BARU
 *
 * Satu-satunya kelas notifikasi yang ada di aplikasi adalah
 * ReservationOverlapRejected, dan kelas itu hanya boleh dibuat oleh
 * ReservationService::approve() ketika pending yang overlap ditolak sistem.
 * Kalau seeder ini menulis baris notifications dengan tangan, maka
 * dataset akan berisi muatan yang tidak pernah bisa terjadi di produksi.
 *
 * Karena itu seeder ini tidak membuat baris baru. Yang diubah hanya read_at
 * pada baris yang memang sudah lahir dari approve(). Dengan begitu isi tabel
 * notifications tetap sepenuhnya berasal dari aplikasi.
 *
 * YANG DIUJI
 *
 * Panel notifikasi di header menampilkan 10 baris terakhir dan badge unread
 * berhenti di 99+. Dua bentuk itu butuh kondisi berbeda:
 *
 *  - satu pengguna dengan lebih dari 99 notifikasi belum dibaca supaya
 *    badge 99+ muncul dan panel yang berisi 10 baris ikut terpotong,
 *  - sebagian pengguna lain dengan campuran unread dan read supaya tombol
 *    "tandai semua dibaca" dan gaya baris yang sudah dibaca ikut terlihat.
 *
 * Notifikasi untuk badge 99+ lahir sendiri dari ReservationVolumeSeeder.
 */
final class NotificationVolumeSeeder extends Seeder
{
    /**
     * Porsi notifikasi yang ditandai sudah dibaca per pengguna.
     *
     * Sisanya sengaja dibiarkan belum dibaca supaya tidak semua akun
     * menampilkan kondisi unread yang sama.
     */
    private const READ_RATIO = 0.6;

    public function run(): void
    {
        $generated = DB::table('notifications')
            ->where('type', ReservationOverlapRejected::class)
            ->count();

        if ($generated < 1) {
            throw new RuntimeException(
                'Tidak ada notifikasi hasil approve(). Jalankan ReservationVolumeSeeder lebih dulu.'
            );
        }

        $this->markPartiallyRead();
        $this->assertCoverage();
    }

    /**
     * Tandai sebagian notifikasi milik setiap pengguna sebagai sudah dibaca.
     *
     * Pengguna dengan notifikasi terbanyak dikecualikan supaya badge 99+
     * tetap terlihat. Penandaan memakai update langsung, sama seperti yang
     * dilakukan NotificationController::markAllRead(), jadi tidak ada model
     * event yang perlu disebutkan di sini.
     */
    private function markPartiallyRead(): void
    {
        $badgeHolder = DB::table('notifications')
            ->select('notifiable_id')
            ->groupBy('notifiable_id')
            ->orderByDesc(DB::raw('count(*)'))
            ->value('notifiable_id');

        $notifiables = DB::table('notifications')
            ->select('notifiable_type', 'notifiable_id')
            ->distinct()
            ->orderBy('notifiable_id')
            ->get();

        foreach ($notifiables as $notifiable) {
            if ($notifiable->notifiable_id === $badgeHolder) {
                continue;
            }

            $ids = DB::table('notifications')
                ->where('notifiable_type', $notifiable->notifiable_type)
                ->where('notifiable_id', $notifiable->notifiable_id)
                ->orderByDesc('created_at')
                ->pluck('id');

            if ($ids->isEmpty()) {
                continue;
            }

            $readCount = (int) round($ids->count() * self::READ_RATIO);

            if ($readCount < 1) {
                continue;
            }

            $readAt = Carbon::now()->subHours($ids->count());

            DB::table('notifications')
                ->whereIn('id', $ids->take($readCount))
                ->update(['read_at' => $readAt]);
        }
    }

    /**
     * Bukti cakupan: ada condition 99+ dan ada campuran unread/read.
     */
    private function assertCoverage(): void
    {
        $rows = DB::table('notifications')
            ->selectRaw('notifiable_id, count(*) total, sum(read_at is null) unread')
            ->groupBy('notifiable_id')
            ->get();

        $badgeHolder = $rows->first(fn ($row) => $row->unread > 99);

        if ($badgeHolder === null) {
            throw new RuntimeException(
                'Tidak ada pengguna dengan lebih dari 99 notifikasi belum dibaca untuk badge 99+.'
            );
        }

        $mixed = $rows->first(fn ($row) => $row->unread > 0 && $row->unread < $row->total);

        if ($mixed === null) {
            throw new RuntimeException('Tidak ada pengguna dengan campuran notifikasi unread dan read.');
        }

        $pending = Reservation::where('status', 'rejected_by_system')->count();

        if ($pending < 1) {
            throw new RuntimeException('Tidak ada rejected_by_system yang menghasilkan notifikasi.');
        }
    }
}
