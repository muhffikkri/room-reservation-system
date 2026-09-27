<?php

namespace App\Services;

use App\Models\Facility;
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
}
