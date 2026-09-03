<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\NoApprovedOverlap;
use App\Rules\SlotAvailable;
use App\Rules\SlotTimeValid;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Satu-satunya pemilik aturan Slot + Overlap (BR-1..BR-7).
 *
 * FormRequest, grid jadwal, dan aksi petugas semuanya bermuara ke sini,
 * sehingga definisi bentrok dan slot tidak pernah diduplikasi.
 */
class ReservationService
{
    /**
     * Ajukan reservasi baru berstatus pending (BR-4, BR-5, BR-6).
     */
    public function create(User $user, Facility $facility, Carbon $start, Carbon $end, string $purpose): Reservation
    {
        Validator::make([
            'facility_id' => $facility->id,
            'date' => $start->toDateString(),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
        ], [
            'start_time' => [new SlotTimeValid],
            'facility_id' => [new SlotAvailable($user->id)],
        ])->validate();

        return DB::transaction(function () use ($user, $facility, $start, $end, $purpose): Reservation {
            $conflict = Reservation::approved()
                ->overlap($facility->id, $start, $end)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new ConflictHttpException('Slot waktu tersebut sudah dipesan (bentrok dengan reservasi yang disetujui).');
            }

            return Reservation::create([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'purpose' => $purpose,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Setujui reservasi pending dalam transaksi + lock (BR-7).
     *
     * Overlap dicek ulang terhadap approved pada fasilitas sama; bila
     * bentrok (kondisi balapan), kembalikan HTTP 409.
     */
    public function approve(Reservation $reservation, User $officer): Reservation
    {
        return DB::transaction(function () use ($reservation, $officer): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new ConflictHttpException('Hanya reservasi pending yang dapat disetujui.');
            }

            try {
                Validator::make(['slot' => true], [
                    'slot' => [new NoApprovedOverlap(
                        $locked->facility_id,
                        $locked->start_time,
                        $locked->end_time,
                        $locked->id,
                    )],
                ])->validate();
            } catch (ValidationException $exception) {
                throw new ConflictHttpException($exception->validator->errors()->first('slot'));
            }

            $locked->update([
                'status' => 'approved',
                'decided_by' => $officer->id,
                'decided_at' => now(),
            ]);

            return $locked->refresh();
        });
    }
}
