<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Satu-satunya pemilik daur hidup Report + ReportUpdate (BR-10, BR-11).
 *
 * Peta transisi (§9.2): baru → diproses → selesai/ditolak. Setiap transisi
 * menulis tepat satu baris audit dalam transaksi yang sama, sehingga status
 * dan riwayat tidak pernah berbohong satu sama lain.
 */
class ReportService
{
    public const DAILY_REPORT_QUOTA = 20;

    /**
     * Buat laporan kerusakan baru oleh pengguna + simpan foto jika ada.
     *
     * @param  array<string, mixed>  $data
     */
    public function createReport(User $user, array $data): Report
    {
        $this->ensureActivePengguna($user);

        $photoPath = null;

        try {
            return DB::transaction(function () use ($user, $data, &$photoPath): Report {
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $today = now();

                $reportsToday = Report::query()
                    ->where('user_id', $lockedUser->id)
                    ->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                    ->count();

                if ($reportsToday >= self::DAILY_REPORT_QUOTA) {
                    throw new TooManyRequestsHttpException(
                        null,
                        'Batas laporan harian telah tercapai. Coba lagi besok.',
                    );
                }

                if (isset($data['photo']) && $data['photo'] instanceof UploadedFile) {
                    $photoPath = $data['photo']->store('reports', 'local');
                }

                return Report::create([
                    'user_id' => $lockedUser->id,
                    'facility_id' => $data['facility_id'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'photo' => $photoPath,
                    'status' => 'baru',
                ]);
            });
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('local')->delete($photoPath);
            }

            throw $exception;
        }
    }

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
        $this->ensureActivePetugas($officer);

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
        $this->ensureActivePetugas($officer);

        return DB::transaction(function () use ($report): Facility {
            $lockedReport = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();

            if ($lockedReport->status !== 'diproses') {
                throw ValidationException::withMessages([
                    'status' => 'Fasilitas hanya dapat ditandai perbaikan saat laporan sedang diproses.',
                ]);
            }

            $facility = Facility::whereKey($lockedReport->facility_id)->lockForUpdate()->firstOrFail();

            if ($facility->status !== 'aktif') {
                throw ValidationException::withMessages([
                    'status' => 'Fasilitas harus berstatus aktif sebelum ditandai perbaikan.',
                ]);
            }

            $facility->update([
                'status' => 'perbaikan',
                'repair_report_id' => $lockedReport->id,
            ]);

            return $facility->refresh();
        });
    }

    /**
     * Kembalikan fasilitas ke aktif setelah laporannya selesai (BR-11).
     */
    public function restoreFacilityToActive(Report $report, User $officer): Facility
    {
        $this->ensureActivePetugas($officer);

        return DB::transaction(function () use ($report): Facility {
            $lockedReport = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();

            if ($lockedReport->status !== 'selesai') {
                throw ValidationException::withMessages([
                    'status' => 'Fasilitas hanya dapat dikembalikan aktif setelah laporannya selesai.',
                ]);
            }

            $facility = Facility::whereKey($lockedReport->facility_id)->lockForUpdate()->firstOrFail();

            if ($facility->status !== 'perbaikan' || (int) $facility->repair_report_id !== $lockedReport->id) {
                throw ValidationException::withMessages([
                    'status' => 'Fasilitas tidak sedang dalam perbaikan oleh laporan ini.',
                ]);
            }

            $facility->update([
                'status' => 'aktif',
                'repair_report_id' => null,
            ]);

            return $facility->refresh();
        });
    }

    private function ensureActivePengguna(User $user): void
    {
        if (! $user->isPengguna() || ! $user->isActive()) {
            throw new AccessDeniedHttpException('Akun tidak memiliki akses untuk membuat laporan.');
        }
    }

    private function ensureActivePetugas(User $user): void
    {
        if (! $user->isPetugas() || ! $user->isActive()) {
            throw new AccessDeniedHttpException('Akun tidak memiliki akses ke operasi petugas.');
        }
    }
}
