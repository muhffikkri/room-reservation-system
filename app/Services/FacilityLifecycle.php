<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns facility status transitions, row locks, and repair linkage (BR-11).
 */
class FacilityLifecycle
{
    public function deactivate(Facility $facility): Facility
    {
        return DB::transaction(function () use ($facility): Facility {
            $locked = Facility::whereKey($facility->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'perbaikan') {
                throw ValidationException::withMessages([
                    'status' => "Fasilitas {$locked->name} sedang dalam perbaikan dan tidak dapat dinonaktifkan sampai penanganannya selesai.",
                ]);
            }

            $locked->update(['status' => 'nonaktif']);

            return $locked->refresh();
        });
    }

    public function activate(Facility $facility): Facility
    {
        return DB::transaction(function () use ($facility): Facility {
            $locked = Facility::whereKey($facility->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'perbaikan') {
                throw ValidationException::withMessages([
                    'status' => "Fasilitas {$locked->name} sedang dalam perbaikan; pengembaliannya ke aktif dilakukan petugas melalui alur laporan.",
                ]);
            }

            $locked->update(['status' => 'aktif']);

            return $locked->refresh();
        });
    }

    /**
     * Tandai fasilitas sebagai perbaikan (BR-11).
     */
    public function markForRepair(Report $report): Facility
    {
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

    public function restoreAfterCompletion(Report $report): Facility
    {
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

    public function restoreAfterRejection(Report $report): void
    {
        DB::transaction(function () use ($report): void {
            $lockedReport = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();

            if ($lockedReport->status !== 'ditolak') {
                throw ValidationException::withMessages([
                    'status' => 'Fasilitas hanya dapat dipulihkan otomatis setelah laporannya ditolak.',
                ]);
            }

            $facility = Facility::whereKey($lockedReport->facility_id)->lockForUpdate()->firstOrFail();

            if ($facility->status === 'perbaikan' && (int) $facility->repair_report_id === $lockedReport->id) {
                $facility->update([
                    'status' => 'aktif',
                    'repair_report_id' => null,
                ]);
            }
        });
    }
}
