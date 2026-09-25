<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\ReservationOverlapRejected;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Mutasi reservasi + transaksi/lock; keputusan ketersediaan slot dimiliki
 * ReservationAvailability (BR-1..BR-7, BR-12).
 */
class ReservationService
{
    /**
     * Jumlah reservasi yang otomatis ditolak oleh approve() terakhir
     * (overlap dengan reservasi yang baru disetujui) — dipakai controller
     * untuk flash notifikasi ke petugas.
     */
    private int $autoRejectedOnApprove = 0;

    public function __construct(
        protected ReservationAvailability $availability,
    ) {}

    /**
     * Jumlah reservasi yang otomatis ditolak sistem pada approve() terakhir.
     */
    public function autoRejectedOnApprove(): int
    {
        return $this->autoRejectedOnApprove;
    }

    /**
     * Tandai reservasi pending yang melewati batas persetujuan (BR-3: kurang
     * dari 60 menit sebelum start_time) sebagai cancelled_by_system.
     *
     * Dijalankan lazy pada setiap akses antrean/dashboard, saat reservasi baru
     * dibuat, dan sebelum approve/reject — sehingga reservasi yang lewat waktu
     * tidak dapat lagi disetujui dan tidak memakan kuota pending. Pembaruan
     * berlaku global agar antrean pengguna maupun petugas konsisten.
     *
     * @return int jumlah reservasi kedaluwarsa — milik $viewer bila diberikan,
     *             seluruhnya bila $viewer null.
     */
    public function expireStale(?User $viewer = null): int
    {
        $stale = Reservation::pending()
            ->where('start_time', '<', $this->availability->leadTimeCutoff());

        $total = (clone $stale)->count();

        if ($total === 0) {
            return 0;
        }

        $expired = $viewer !== null
            ? (clone $stale)->where('user_id', $viewer->id)->count()
            : $total;

        $stale->update([
            'status' => 'cancelled_by_system',
            'cancel_reason' => 'Otomatis dibatalkan sistem: melewati batas persetujuan (60 menit sebelum waktu mulai).',
            'decided_at' => now(),
        ]);

        return $expired;
    }

    /**
     * Ajukan reservasi baru berstatus pending (BR-4, BR-5, BR-6).
     */
    public function create(User $user, Facility $facility, Carbon $start, Carbon $end, string $purpose): Reservation
    {
        $this->ensureActivePengguna($user);

        return DB::transaction(function () use ($user, $facility, $start, $end, $purpose): Reservation {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->ensureActivePengguna($lockedUser);

            $lockedFacility = Facility::whereKey($facility->id)->lockForUpdate()->firstOrFail();

            // Reservasi pending yang sudah melewati batas persetujuan dibersihkan
            // lebih dulu agar tidak memakan kuota pending milik pengguna (BR-3).
            $this->expireStale($lockedUser);

            // Semua aturan yang bergantung pada state database berjalan setelah
            // row user dan fasilitas dikunci, sehingga validasi dan insert tidak
            // dapat diselipkan request paralel (BR-4, BR-5, BR-6, BR-7).
            // Bentuk slot (BR-1, BR-2) dan ketersediaan (BR-3..BR-6) dimiliki
            // ReservationAvailability; service memetakan hasilnya ke pesan
            // validasi agar alur HTTP tidak berubah.
            $this->assertSlotShape($start, $end);
            $this->assertAvailability($lockedFacility, $lockedUser, $start, $end);

            Validator::make([
                'purpose' => $purpose,
            ], [
                'purpose' => ['required', 'string', 'min:10', 'max:255'],
            ])->validate();

            // Bentrok yang muncul dari writer di luar service berarti kondisi
            // balapan; tetap jawab 409 agar caller tidak menganggap booking sukses.
            $conflict = Reservation::approved()
                ->overlap($lockedFacility->id, $start, $end)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new ConflictHttpException('Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.');
            }

            return Reservation::create([
                'user_id' => $lockedUser->id,
                'facility_id' => $lockedFacility->id,
                'purpose' => $purpose,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Setujui reservasi pending dalam transaksi + lock (BR-7, BR-12).
     *
     * Overlap dicek ulang terhadap approved pada fasilitas sama; bila
     * bentrok (kondisi balapan), kembalikan HTTP 409. Fasilitas juga
     * dikunci dan harus berstatus aktif: persetujuan yang diberikan
     * setelah fasilitas rusak melanggar BR-12.
     *
     * Setelah disetujui, seluruh reservasi pending pada fasilitas sama yang
     * intervalnya overlap (termasuk bersinggungan) otomatis ditolak sistem
     * (rejected_by_system) dan pemiliknya diberi notifikasi in-app.
     */
    public function approve(Reservation $reservation, User $officer): Reservation
    {
        $this->ensureActivePetugas($officer);

        // Reservasi lewat batas dikonversi ke cancelled_by_system lebih dulu
        // sehingga guard status di bawah menjawab 409, bukan menyetujui.
        $this->expireStale();

        $this->autoRejectedOnApprove = 0;

        return DB::transaction(function () use ($reservation, $officer): Reservation {
            // Sistem mengunci baris ini agar dua petugas yang menekan
            // approve bersamaan tidak meloloskan dua pemenang (BR-7).
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new ConflictHttpException('Hanya reservasi pending yang dapat disetujui.');
            }

            // Sistem mengunci fasilitas agar perubahan status (misal ke
            // perbaikan, BR-11) tidak menyelinap di tengah persetujuan.
            $facility = Facility::whereKey($locked->facility_id)->lockForUpdate()->firstOrFail();

            if ($facility->status !== 'aktif') {
                throw new ConflictHttpException('Fasilitas sedang berstatus '.$facility->status.' sehingga reservasi tidak dapat disetujui.');
            }

            // Cek ulang overlap terhadap approved pada fasilitas sama (BR-7). Kondisi
            // balapan dari writer lain dijawab 409 agar tidak ada dua pemenang.
            if ($this->availability->hasBlockingOverlap(
                $locked->facility_id,
                $locked->start_time,
                $locked->end_time,
                $locked->id,
            )) {
                throw new ConflictHttpException('Slot waktu tersebut sudah dipesan (bentrok dengan reservasi yang disetujui).');
            }

            $locked->update([
                'status' => 'approved',
                'decided_by' => $officer->id,
                'decided_at' => now(),
            ]);

            $this->autoRejectedOnApprove = $this->rejectOverlappingPendings($locked);

            return $locked->refresh();
        });
    }

    /**
     * Tolak otomatis seluruh reservasi pending pada fasilitas sama yang
     * intervalnya overlap (interval tertutup — termasuk yang bersinggungan)
     * dengan reservasi yang baru disetujui.
     *
     * Berjalan di dalam transaksi approve(); pemilik tiap reservasi yang
     * ditolak menerima notifikasi in-app "Maaf, fasilitas ini sudah
     * dipesan pada jam yang sama (atau overlap)...". Mengembalikan jumlah
     * reservasi yang ditolak.
     */
    private function rejectOverlappingPendings(Reservation $approved): int
    {
        $losers = Reservation::pending()
            ->overlap($approved->facility_id, $approved->start_time, $approved->end_time)
            ->whereKeyNot($approved->id)
            ->lockForUpdate()
            ->get();

        if ($losers->isEmpty()) {
            return 0;
        }

        foreach ($losers as $loser) {
            $loser->update([
                'status' => 'rejected_by_system',
                'reject_reason' => 'Otomatis ditolak sistem: jadwal bertabrakan dengan reservasi #'.$approved->id.' yang disetujui petugas.',
                'decided_at' => now(),
            ]);

            $loser->user?->notify(new ReservationOverlapRejected($loser));
        }

        return $losers->count();
    }

    /**
     * Tolak reservasi pending dengan alasan wajib (BR-9).
     *
     * Hanya reservasi pending yang dapat ditolak. Alasan wajib diisi
     * minimal 10 karakter dan tersimpan agar tampil di detail.
     */
    public function reject(Reservation $reservation, User $officer, string $reason): Reservation
    {
        $this->ensureActivePetugas($officer);

        $this->expireStale();

        return DB::transaction(function () use ($reservation, $officer, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new ConflictHttpException('Hanya reservasi pending yang dapat ditolak.');
            }

            $locked->update([
                'status' => 'rejected',
                'reject_reason' => $reason,
                'decided_by' => $officer->id,
                'decided_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Batalkan reservasi oleh petugas dengan alasan wajib (BR-9, BR-16).
     *
     * Petugas dapat membatalkan reservasi approved (mis. fasilitas masuk
     * perbaikan) maupun pending. Alasan wajib diisi minimal 10 karakter
     * dan tampil di detail reservasi.
     */
    public function cancel(Reservation $reservation, User $officer, string $reason): Reservation
    {
        $this->ensureActivePetugas($officer);

        return DB::transaction(function () use ($reservation, $officer, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, ['pending', 'approved'], true)) {
                throw new ConflictHttpException('Hanya reservasi pending atau approved yang dapat dibatalkan petugas.');
            }

            $locked->update([
                'status' => 'cancelled_by_officer',
                'cancel_reason' => $reason,
                'decided_by' => $officer->id,
                'decided_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Batalkan reservasi oleh pengguna pemilik (BR-8).
     *
     * Hanya pemilik yang dapat membatalkan reservasi miliknya yang berstatus
     * pending atau approved, dan minimal 1 jam sebelum start_time.
     */
    public function cancelByUser(Reservation $reservation, User $user, ?string $reason = null): Reservation
    {
        $this->ensureActivePengguna($user);

        return DB::transaction(function () use ($reservation, $user, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $user->id) {
                throw new AccessDeniedHttpException('Anda hanya dapat membatalkan reservasi milik Anda sendiri.');
            }

            if (! in_array($locked->status, ['pending', 'approved'], true)) {
                throw new ConflictHttpException('Hanya reservasi berstatus pending atau approved yang dapat dibatalkan.');
            }

            if ($locked->start_time->isBefore(now()->addHour())) {
                throw new ConflictHttpException('Reservasi hanya dapat dibatalkan paling lambat 1 jam sebelum waktu mulai.');
            }

            $locked->update([
                'status' => 'cancelled_by_user',
                'cancel_reason' => $reason,
            ]);

            return $locked->refresh();
        });
    }

    private function assertSlotShape(Carbon $start, Carbon $end): void
    {
        $errors = $this->availability->slotTimeErrors($start, $end);

        if ($errors !== []) {
            throw ValidationException::withMessages(['slot' => $errors[0]]);
        }
    }

    private function assertAvailability(Facility $facility, User $user, Carbon $start, Carbon $end): void
    {
        $messages = array_values(array_filter([
            $this->availability->facilityUnavailabilityError($facility),
            $this->availability->leadTimeError($start),
            $this->availability->pendingQuotaError($user->id, $start),
            $this->availability->overlapError($facility->id, $start, $end),
        ]));

        if ($messages !== []) {
            throw ValidationException::withMessages(['facility_id' => $messages]);
        }
    }

    private function ensureActivePengguna(User $user): void
    {
        if (! $user->isPengguna() || ! $user->isActive()) {
            throw new AccessDeniedHttpException('Akun tidak memiliki akses ke operasi pengguna.');
        }
    }

    private function ensureActivePetugas(User $user): void
    {
        if (! $user->isPetugas() || ! $user->isActive()) {
            throw new AccessDeniedHttpException('Akun tidak memiliki akses ke operasi petugas.');
        }
    }
}
