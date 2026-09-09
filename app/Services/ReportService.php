<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya pemilik daur hidup Report + ReportUpdate (BR-10, BR-11).
 *
 * Peta transisi (§9.2): baru → diproses → selesai/ditolak. Setiap transisi
 * menulis tepat satu baris audit dalam transaksi yang sama, sehingga status
 * dan riwayat tidak pernah berbohong satu sama lain.
 */
class ReportService
{
    /**
     * Status tujuan yang legal dari setiap status (§9.2).
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        'baru' => ['diproses'],
        'diproses' => ['selesai', 'ditolak'],
        'selesai' => [],
        'ditolak' => [],
    ];

    /**
     * Status penutup yang mewajibkan catatan resolusi (BR-10).
     *
     * @var list<string>
     */
    public const CLOSING_STATUSES = ['selesai', 'ditolak'];

    /**
     * @return list<string>
     */
    public static function allowedTransitions(string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }

    /**
     * Pindahkan status laporan dan catat jejak auditnya (BR-10).
     */
    public function transition(Report $report, User $officer, string $newStatus, ?string $note = null): Report
    {
        return DB::transaction(function () use ($report, $officer, $newStatus, $note): Report {
            // Kunci baris laporan agar dua petugas tidak memproses laporan yang sama bersamaan.
            $locked = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            // Tolak lompatan status yang tidak ada di peta §9.2 (misal baru langsung ke selesai).
            if (! in_array($newStatus, self::allowedTransitions($from), true)) {
                throw ValidationException::withMessages([
                    'status' => "Transisi status {$from} ke {$newStatus} tidak diperbolehkan.",
                ]);
            }

            // Menutup laporan wajib membawa catatan resolusi minimal 10 karakter (BR-10).
            if (in_array($newStatus, self::CLOSING_STATUSES, true) && ($note === null || mb_strlen(trim($note)) < 10)) {
                throw ValidationException::withMessages([
                    'resolution_note' => 'Catatan resolusi wajib diisi (minimal 10 karakter) saat menutup laporan.',
                ]);
            }

            // Ubah status dan catat penangan dalam transaksi yang sama agar tidak berbohong satu sama lain.
            $locked->update([
                'status' => $newStatus,
                'resolution_note' => in_array($newStatus, self::CLOSING_STATUSES, true) ? $note : $locked->resolution_note,
                'handled_by' => $officer->id,
                'handled_at' => now(),
            ]);

            // Tulis tepat satu baris jejak audit untuk transisi ini; riwayat hanya boleh bertambah.
            ReportUpdate::create([
                'report_id' => $locked->id,
                'user_id' => $officer->id,
                'old_status' => $from,
                'new_status' => $newStatus,
                'note' => $note,
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Tandai fasilitas laporan sebagai perbaikan (BR-11).
     */
    public function markFacilityForRepair(Report $report, User $officer): Facility
    {
        // Ambil data terbaru agar pengecekan status tidak memakai data basi.
        $report = $report->fresh() ?? $report;

        // Fasilitas hanya boleh ditandai perbaikan saat laporan sedang ditangani (diproses, BR-11).
        if ($report->status !== 'diproses') {
            throw ValidationException::withMessages([
                'status' => 'Fasilitas hanya dapat ditandai perbaikan saat laporan sedang diproses.',
            ]);
        }

        $facility = $report->facility;
        $facility->update(['status' => 'perbaikan']);

        return $facility->refresh();
    }

    /**
     * Kembalikan fasilitas ke aktif setelah laporannya selesai (BR-11).
     */
    public function restoreFacilityToActive(Report $report, User $officer): Facility
    {
        // Ambil data terbaru agar pengecekan status tidak memakai data basi.
        $report = $report->fresh() ?? $report;

        // Fasilitas hanya boleh dikembalikan aktif setelah laporannya selesai (BR-11).
        if ($report->status !== 'selesai') {
            throw ValidationException::withMessages([
                'status' => 'Fasilitas hanya dapat dikembalikan aktif setelah laporannya selesai.',
            ]);
        }

        $facility = $report->facility;

        // Jangan ubah apa pun jika fasilitas tidak sedang dalam perbaikan.
        if ($facility->status !== 'perbaikan') {
            throw ValidationException::withMessages([
                'status' => 'Fasilitas tidak sedang dalam perbaikan.',
            ]);
        }

        $facility->update(['status' => 'aktif']);

        return $facility->refresh();
    }
}
