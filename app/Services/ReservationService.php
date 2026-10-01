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
     * Panjang minimum alasan yang disimpan. Dua angka karena aturan memang
     * berbeda: §5 memberi pengguna 5 karakter, BR-9 memberi petugas 10.
     */
    public const MIN_USER_CANCEL_REASON = 5;

    public const MIN_OFFICER_REASON = 10;

    public function __construct(
        protected ReservationAvailability $availability,
    ) {}

    /**
     * Tandai reservasi pending yang melewati batas persetujuan (BR-3: kurang
     * dari 60 menit sebelum start_time) sebagai cancelled_by_system.
     *
     * Dijalankan lazy pada setiap akses antrean/dashboard, saat reservasi baru
     * dibuat, dan sebelum approve/reject — sehingga reservasi yang lewat waktu
     * tidak dapat lagi disetujui dan tidak memakan kuota pending.
     *
     * Pembaruan hanya menyentuh baris milik $viewer bila diberikan: membuka
     * halaman satu pengguna tidak boleh membatalkan reservasi pengguna lain.
     * $viewer null dipakai petugas untuk menyapu seluruh antrean.
     *
     * @return int jumlah reservasi kedaluwarsa yang diproses pemanggilan ini.
     */
    public function expireStale(?User $viewer = null): int
    {
        $stale = Reservation::pending()
            ->where('start_time', '<', $this->availability->leadTimeCutoff())
            ->when($viewer !== null, fn ($query) => $query->where('user_id', $viewer->id));

        $expired = (clone $stale)->count();

        if ($expired === 0) {
            return 0;
        }

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
            $this->assertBookableWindow($start);
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
     * intervalnya overlap (interval setengah terbuka — yang hanya bersinggungan
     * boleh berurutan) otomatis ditolak sistem (rejected_by_system) dan
     * pemiliknya diberi notifikasi in-app.
     */
    public function approve(Reservation $reservation, User $officer): int
    {
        $this->ensureActivePetugas($officer);

        // Reservasi lewat batas dikonversi ke cancelled_by_system lebih dulu
        // sehingga guard status di bawah menjawab 409, bukan menyetujui.
        $this->expireStale();

        return DB::transaction(function () use ($reservation, $officer): int {
            // Sistem mengunci baris ini agar dua petugas yang menekan
            // approve bersamaan tidak meloloskan dua pemenang (BR-7).
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                throw new ConflictHttpException('Hanya reservasi pending yang dapat disetujui.');
            }

            $this->assertNotStalePending($locked);

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

            return $this->rejectOverlappingPendings($locked);
        });
    }

    /**
     * Tolak otomatis seluruh reservasi pending pada fasilitas sama yang
     * intervalnya overlap (interval setengah terbuka — yang hanya bersinggungan
     * boleh berurutan) dengan reservasi yang baru disetujui.
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
        $this->assertReason('reason', $reason, self::MIN_OFFICER_REASON);

        $this->expireStale();

        return DB::transaction(function () use ($reservation, $officer, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                throw new ConflictHttpException('Hanya reservasi pending yang dapat ditolak.');
            }

            $this->assertNotStalePending($locked);

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
        $this->assertReason('cancel_reason', $reason, self::MIN_OFFICER_REASON);

        return DB::transaction(function () use ($reservation, $officer, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isCancellable()) {
                throw new ConflictHttpException('Hanya reservasi pending atau approved yang dapat dibatalkan petugas.');
            }

            // Hanya pending yang bisa sudah kedaluwarsa: approved yang tinggal
            // sebentar tetap boleh dibatalkan petugas (BR-16).
            if ($locked->isPending()) {
                $this->assertNotStalePending($locked);
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
     * Determine whether a user may cancel a reservation under BR-8.
     */
    public function canCancelByUser(Reservation $reservation, User $user): bool
    {
        return $user->isActive()
            && $user->isPengguna()
            && $reservation->user_id === $user->id
            && $reservation->isCancellable()
            && ! $reservation->start_time->isBefore(now()->addHour());
    }

    /**
     * Batalkan reservasi oleh pengguna pemilik (BR-8).
     *
     * Hanya pemilik yang dapat membatalkan reservasi miliknya yang berstatus
     * pending atau approved, dan minimal 1 jam sebelum start_time.
     */
    public function cancelByUser(Reservation $reservation, User $user, string $reason): Reservation
    {
        $this->ensureActivePengguna($user);
        $this->assertReason('cancel_reason', $reason, self::MIN_USER_CANCEL_REASON);

        return DB::transaction(function () use ($reservation, $user, $reason): Reservation {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($locked->user_id !== $user->id) {
                throw new AccessDeniedHttpException('Anda hanya dapat membatalkan reservasi milik Anda sendiri.');
            }

            if (! $locked->isCancellable()) {
                throw new ConflictHttpException('Hanya reservasi berstatus pending atau approved yang dapat dibatalkan.');
            }

            if (! $this->canCancelByUser($locked, $user)) {
                throw new ConflictHttpException('Reservasi hanya dapat dibatalkan paling lambat 1 jam sebelum waktu mulai.');
            }

            $locked->update([
                'status' => 'cancelled_by_user',
                'cancel_reason' => $reason,
                'decided_at' => now(),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Penjaga BR-3 yang dijalankan di dalam lock, setelah baris dikunci.
     *
     * Sapuan sebelum approve/reject berjalan sebelum transaksi, jadi batas bisa
     * saja sudah terlewati ketika lock diperoleh. Tanpa penjaga di sini,
     * reservasi bisa disetujui kurang dari 60 menit sebelum mulai — tepat hal
     * yang BR-3 cegah.
     *
     * ponytail: jendela balapan ini tidak bisa diuji regresi tanpa celah jam
     * yang dapat disuntikkan; repo memanggil Carbon::now() langsung, dan
     * travel() ke depan justru membuat sapuan yang lebih dulu menangkap baris
     * itu. Butuh seam jam, yaitu refactor tersendiri.
     *
     * @param  Reservation  $locked  baris pending yang sudah dikunci
     */
    private function assertNotStalePending(Reservation $locked): void
    {
        if ($this->availability->isWithinLeadTime($locked->start_time)) {
            throw new ConflictHttpException('Reservasi sudah melewati batas persetujuan (1 jam sebelum waktu mulai) sehingga tidak dapat diproses.');
        }
    }

    /**
     * Penjaga panjang alasan di batas bersama.
     *
     * Lapisan request sudah memvalidasi, tetapi pemanggil internal
     * (command, seeder, pengujian) melewati request itu. Tanpa penjaga di
     * sini, alasan kosong atau null akan tersimpan dan membatalkan
     * reservasi tanpa keterangan (§5 mewajibkan minimal 5 karakter).
     *
     * @param  string  $attribute  nama field pada pemanggil, agar pesan
     *                             validasi muncul pada field yang benar
     */
    private function assertReason(string $attribute, ?string $reason, int $minimum): void
    {
        Validator::make(
            [$attribute => $reason],
            [$attribute => ['required', 'string', 'min:'.$minimum, 'max:255']],
        )->validate();
    }

    private function assertSlotShape(Carbon $start, Carbon $end): void
    {
        $errors = $this->availability->slotTimeErrors($start, $end);

        if ($errors !== []) {
            throw ValidationException::withMessages(['slot' => $errors[0]]);
        }
    }

    /**
     * Penjaga batas pemesanan ke depan (BR-3) di batas bersama, bukan hanya
     * di form: pemanggil internal service lewat jalan yang sama.
     *
     * Dilempar pada kunci `date` karena ini bukan masalah fasilitas.
     */
    private function assertBookableWindow(Carbon $start): void
    {
        $error = $this->availability->lookaheadError($start);

        if ($error !== null) {
            throw ValidationException::withMessages(['date' => [$error]]);
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
