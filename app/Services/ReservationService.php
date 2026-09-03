<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\BookingLeadTime;
use App\Rules\FacilityBookable;
use App\Rules\NoApprovedOverlap;
use App\Rules\PendingQuota;
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
        // Aturan menerima Carbon langsung: tidak ada bongkar-pasang string,
        // tidak ada parse ulang, tidak ada lolos diam-diam.
        Validator::make([
            'slot' => true,
            'facility_id' => $facility->id,
        ], [
            'slot' => [new SlotTimeValid($start, $end)],
            'facility_id' => [
                new FacilityBookable($facility->id),
                new BookingLeadTime($start),
                new PendingQuota($user->id, $start),
                new NoApprovedOverlap($facility->id, $start, $end),
            ],
        ])->validate();

        return DB::transaction(function () use ($user, $facility, $start, $end, $purpose): Reservation {
            // Sistem memeriksa ulang bentrok di dalam transaksi karena
            // reservasi lain dapat lolos validasi di atas lebih dulu.
            // Bentrok di titik ini berarti kondisi balapan, sehingga
            // sistem menjawab 409, bukan error validasi (BR-7).
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
            // Sistem mengunci baris ini agar dua petugas yang menekan
            // approve bersamaan tidak meloloskan dua pemenang (BR-7).
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
