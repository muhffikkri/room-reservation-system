<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationAvailability;
use App\Services\ReservationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Volume reservasi untuk menguji daftar pemesanan pengguna, antrean
 * petugas, rekap okupansi, jadwal publik, dan inbox notifikasi.
 *
 * PEMBELAJAN HISTORIS DAN MASA DEPAN
 * ReservationService::create() menolak waktu mulai yang sudah lewat
 * (lead time 60 menit) dan approve()/reject() menolak pending yang lewat
 * batas persetujuan. Jadi pemesanan di masa depan harus benar-benar dibuat
 * lewat service, sedangkan riwayat tidak mungkin.
 *
 * Riwayat karena itu ditulis langsung, tapi hanya untuk nilai yang memang
 * boleh diisi pengguna atau petugas: status, alasan penolapan atau
 * pembatalan, dan decided_by. Dua status milik sistem TIDAK ditulis manual
 * di sini:
 *
 *  - rejected_by_system lahir dari approve() yang menabrak pending lain, jadi
 *    skenario ini dibuat lewat approve() sungguhan.
 *  - cancelled_by_system lahir dari expireStale() yang dijalankan sungguhan,
 *    termasuk kasus batas tepat di leadTimeCutoff().
 *
 * Batas jam
 *
 * Setiap penolakan dan pembatalan selalu punya alasan, dan setiap status
 * yang butuh petugas selalu punya decided_by.
 *
 * Kuota pending
 *
 * ReservationAvailability::pendingQuotaError membatasi dua pending per
 * pengguna per hari. Perebutan slot memakai pengguna berbeda pada slot yang
 * sama, jadi kuota itu tetap aman: yang dihitung per pengguna, bukan per
 * fasilitas.
 */
final class ReservationVolumeSeeder extends Seeder
{
    /**
     * Jumlah hari ke depan yang diisi.
     */
    private const FUTURE_DAYS = 8;

    /**
     * Jumlah fasilitas aktif yang dipakai per hari.
     */
    private const FACILITIES_PER_DAY = 8;

    /**
     * Jumlah pemesanan per fasilitas per hari.
     */
    private const RESERVATIONS_PER_FACILITY = 2;

    /**
     * Jumlah hari ke belakang untuk riwayat.
     */
    private const HISTORY_DAYS = 45;

    /**
     * Jumlah baris riwayat per hari.
     */
    private const HISTORY_PER_DAY = 6;

    /**
     * Jumlah pengguna yang saling berebut satu slot yang sama. Satu yang
     * disetujui, sisanya menjadi rejected_by_system.
     */
    private const CONTENDERS = 6;

    /**
     * Jumlah hari perebutan slot untuk satu pengguna yang selalu kalah.
     *
     * Panel notifikasi header memakai limit(10) dan badge unread berhenti
     * di 99+. Dua batas itu hanya bisa diuji oleh pengguna yang punya lebih
     * dari 99 notifikasi belum dibaca, jadi nilainya sengaja di atas 100.
     */
    private const UNREAD_BADGE_DAYS = 105;

    /**
     * Jumlah hari yang dipakai untuk skenario perebutan slot bersama.
     */
    private const CONTENTION_DAYS = 3;

    /**
     * Segmen yang diulang untuk membangun tujuan sepanjang 255 karakter,
     * yaitu panjang maksimum yang diizinkan aturan purpose.
     */
    private const LONG_PURPOSE_SEGMENT = 'Rapat koordinasi tim pengajar untuk evaluasi kegiatan akademik semester ini. ';

    /**
     * Tujuan singkat, memakai ASCII dan tanpa spasi ganda supaya batas
     * minimum sepuluh karakter benar-benar diuji.
     *
     * @var list<string>
     */
    private const MINIMUM_PURPOSE = 'Rapat tim.';

    /**
     * @var list<string>
     */
    private const SHORT_PURPOSES = [
        'Diskusi kelompok kecil',
        'Rutin mingguan',
        'Konsultasi daring',
    ];

    /**
     * Awal tujuan pemesanan yang dilanjutkan keterangan singkat.
     *
     * @var list<string>
     */
    private const PURPOSE_HEADS = [
        'Rapat koordinasi',
        'Latihan tim',
        'Ujian akhir',
        'Sesi diskusi',
        'Pelatihan karyawan',
    ];

    public function run(): void
    {
        $service = app(ReservationService::class);
        $availability = app(ReservationAvailability::class);

        $users = VolumeRoster::pengguna();
        $officers = VolumeRoster::petugas();
        $facilities = VolumeRoster::aktifFacilities();

        if ($users->isEmpty() || $officers->isEmpty() || $facilities->isEmpty()) {
            throw new RuntimeException(
                'UserVolumeSeeder, seeder verifikasi, dan FacilityVolumeSeeder harus dijalankan sebelum ReservationVolumeSeeder.'
            );
        }

        if ($users->count() < self::CONTENDERS) {
            throw new RuntimeException('Pengguna aktif tidak cukup untuk skenario perebutan slot.');
        }

        $this->seedFuture($service, $availability, $users, $officers, $facilities);
        $this->seedContention($service, $availability, $users, $officers, $facilities);
        $this->seedHistory($users, $officers, $facilities, $availability);
        $this->seedExpiryBoundary($service, $users, $facilities, $availability);
        $this->assertCoverage();
    }

    /**
     * Fasilitas yang dipakai pada satu hari.
     *
     * Pergantian hari memakai langkah yang relatif prima terhadap jumlah
     * fasilitas sehingga setiap hari memakai potongan berbeda dan bukan
     * hanya mengulang tiga potongan pertama.
     *
     * @param  Collection<int, Facility>  $facilities
     * @return Collection<int, Facility>
     */
    private function facilitiesForDay(Collection $facilities, int $day): Collection
    {
        $step = $this->coprimeStep($facilities->count());

        return collect(range(0, self::FACILITIES_PER_DAY - 1))
            ->map(fn (int $offset): Facility => $facilities[($day * $step + $offset) % $facilities->count()]);
    }

    /**
     * Langkah yang relatif prima terhadap $size agar memutar seluruh
     * koleksi sebelum mengulang, tanpa membuat primanya sendiri.
     */
    private function coprimeStep(int $size): int
    {
        for ($step = 7; $step < $size; $step++) {
            if ($this->greatestCommonDivisor($step, $size) === 1) {
                return $step;
            }
        }

        return 1;
    }

    private function greatestCommonDivisor(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a;
    }

    /**
     * Pemesanan masa depan, semuanya lewat ReservationService.
     *
     * Slot diambil dari jam operasional yang dimiliki
     * ReservationAvailability, jadi seeder ini tidak pernah menulis jam
     * buka-tutup sendiri.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, User>  $officers
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedFuture(
        ReservationService $service,
        ReservationAvailability $availability,
        Collection $users,
        Collection $officers,
        Collection $facilities,
    ): void {
        $shape = VolumeRoster::slotShape();
        $sequence = 0;

        for ($day = 1; $day <= self::FUTURE_DAYS; $day++) {
            $dayStart = $availability->dayStart(Carbon::today()->addDays($day));
            $dayFacilities = $this->facilitiesForDay($facilities, $day);

            foreach ($dayFacilities as $facilityIndex => $facility) {
                for ($slot = 0; $slot < self::RESERVATIONS_PER_FACILITY; $slot++) {
                    $sequence++;

                    // Slot digeser per fasilitas supaya dua hari tidak memakai
                    // pola slot yang identik dan daftar antrean terlihat hidup.
                    // Sisanya dipakai untuk pemesanan pada hari berikutnya.
                    $span = intdiv($shape['count'] - 4, self::RESERVATIONS_PER_FACILITY);
                    $slotIndex = ($day * 3 + $facilityIndex + $slot * $span) % ($shape['count'] - 4);
                    $start = $dayStart->copy()->addMinutes($slotIndex * $shape['minutes']);
                    $end = $start->copy()->addMinutes($shape['minutes'] * (1 + ($sequence % 2)));

                    $pending = $service->create(
                        $users[$sequence % $users->count()],
                        $facility,
                        $start,
                        $end,
                        $this->purpose($sequence),
                    );

                    $outcome = $sequence % 4;

                    if ($outcome === 0) {
                        $service->approve($pending, $officers[$sequence % $officers->count()]);

                        continue;
                    }

                    if ($outcome === 1) {
                        $service->reject(
                            $pending,
                            $officers[$sequence % $officers->count()],
                            $this->officerReason($sequence),
                        );
                    }

                    // Sisa sengaja dibiarkan pending untuk mengisi antrean petugas.
                }
            }
        }
    }

    /**
     * Skenario perebutan slot: beberapa pengguna membuat pending pada slot
     * yang sama persis di fasilitas yang sama, lalu satu disetujui.
     *
     * approve() otomatis menolak pending lain yang overlap menjadi
     * rejected_by_system dan memberi notifikasi pada pemiliknya. Semua
     * rejected_by_system dan notifikasi overlap di dataset ini berasal dari
     * sini, bukan dari penulisan manual.
     *
     * Dua bentuk dipakai. Bentuk pertama memberi satu pengguna khusus seratus
     * notifikasi belum dibaca supaya badge '99+' di header teruji; bentuk
     * kedua menyebarkan sisa losers ke pengguna lain supaya tiap daftar
     * notifikasi tidak seragam.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, User>  $officers
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedContention(
        ReservationService $service,
        ReservationAvailability $availability,
        Collection $users,
        Collection $officers,
        Collection $facilities,
    ): void {
        $this->seedUnreadBadgeScenario($service, $availability, $users, $officers, $facilities);
        $this->seedSharedContention($service, $availability, $users, $officers, $facilities);
    }

    /**
     * Bentuk pertama: satu pengguna tetap kalah setiap hari.
     *
     * Panel notifikasi di header menampilkan maksimal sepuluh baris dan
     * badge unread berhenti di 99+, jadi hanya pengguna dengan seratus
     * notifikasi belum dibaca yang bisa menguji dua batas itu sekaligus.
     * Biaya paling murah: dua reservasi per hari, satu yang menang dan satu
     * yang kalah, sehingga pengguna target hanya memakai dua baris per hari.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, User>  $officers
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedUnreadBadgeScenario(
        ReservationService $service,
        ReservationAvailability $availability,
        Collection $users,
        Collection $officers,
        Collection $facilities,
    ): void {
        $shape = VolumeRoster::slotShape();
        $target = $users[0];
        $firstDay = self::FUTURE_DAYS + 1;

        for ($offset = 0; $offset < self::UNREAD_BADGE_DAYS; $offset++) {
            $dayStart = $availability->dayStart(Carbon::today()->addDays($firstDay + $offset));
            $facility = $facilities[($offset * 5) % $facilities->count()];
            $start = $dayStart->copy()->addMinutes((2 + $offset % 10) * $shape['minutes']);
            $end = $start->copy()->addMinutes($shape['minutes'] * 2);

            // Pian darin memakai slot yang sama persis, jadi keduanya pending
            // dulu dan baru wrestle saat salah satunya disetujui.
            $winner = $service->create(
                $users[($offset % max(1, $users->count() - 1)) + 1],
                $facility,
                $start,
                $end,
                $this->purpose(2000 + $offset),
            );

            $loser = $service->create(
                $target,
                $facility,
                $start,
                $end,
                $this->purpose(3000 + $offset),
            );

            $service->approve($winner, $officers[$offset % $officers->count()]);

            // Pemegang yang kalah harus benar-benar berubah, bukan tetap
            // pending karenaapprove gagal.
            if ($loser->refresh()->status !== 'rejected_by_system') {
                throw new RuntimeException('Skenario badge tidak menghasilkan rejected_by_system pada pemilik yang kalah.');
            }
        }
    }

    /**
     * Bentuk kedua: perebutan slot dengan beberapa pemohon sekaligus pada
     * hari yang sama, supaya lebih dari satu pengguna punya notifikasi.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, User>  $officers
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedSharedContention(
        ReservationService $service,
        ReservationAvailability $availability,
        Collection $users,
        Collection $officers,
        Collection $facilities,
    ): void {
        $shape = VolumeRoster::slotShape();
        $firstDay = self::FUTURE_DAYS + self::UNREAD_BADGE_DAYS + 1;

        for ($offset = 0; $offset < self::CONTENTION_DAYS; $offset++) {
            $day = $firstDay + $offset;
            $facility = $facilities[($offset * 5) % $facilities->count()];
            $dayStart = $availability->dayStart(Carbon::today()->addDays($day));
            $start = $dayStart->copy()->addMinutes((6 + $offset % 8) * $shape['minutes']);
            $end = $start->copy()->addMinutes($shape['minutes'] * 2);

            $contenders = [];

            foreach (range(0, self::CONTENDERS - 1) as $index) {
                $contenders[] = $service->create(
                    $users[$index],
                    $facility,
                    $start,
                    $end,
                    $this->purpose(1000 + $offset * 10 + $index),
                );
            }

            $service->approve($contenders[0], $officers[$offset % $officers->count()]);
        }
    }

    /**
     * Riwayat 45 hari ke belakang, ditulis langsung karena service tidak
     * menerima waktu yang sudah lewat.
     *
     * Hanya empat status yang boleh lahir dari nilai manual, yaitu status
     * yang tidak membawa alasan milik sistem. Penolakan atau pembatalan
     * oleh petugas memakai 'rejected' atau 'cancelled_by_officer', sedangkan
     * pembatalan oleh pengguna memakai 'cancelled_by_user'.
     *
     * @param  Collection<int, User>  $officers
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedHistory(
        Collection $users,
        Collection $officers,
        Collection $facilities,
        ReservationAvailability $availability,
    ): void {
        $shape = VolumeRoster::slotShape();
        $sequence = 0;

        $statuses = ['approved', 'rejected', 'cancelled_by_user', 'cancelled_by_officer'];

        for ($day = 1; $day <= self::HISTORY_DAYS; $day++) {
            for ($i = 0; $i < self::HISTORY_PER_DAY; $i++) {
                $sequence++;

                $date = Carbon::today()->subDays($day);
                $dayStart = $availability->dayStart($date);
                $slotIndex = ($day * 7 + $i * 3) % ($shape['count'] - 2);
                $start = $dayStart->copy()->addMinutes($slotIndex * $shape['minutes']);
                $end = $start->copy()->addMinutes($shape['minutes']);
                $status = $statuses[$sequence % count($statuses)];
                $officer = $officers[$sequence % $officers->count()];

                // Waktu capstone dibuat agak setelah waktu mulai supaya tidak
                // terlihat seperti decided sebelum pemesanan.
                $decidedAt = $start->copy()->subHours(2 + ($sequence % 30));

                $row = [
                    'user_id' => $users[$sequence % $users->count()]->id,
                    'facility_id' => $facilities[$sequence % $facilities->count()]->id,
                    'purpose' => $this->purpose($sequence),
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $status,
                    'decided_by' => $officer->id,
                    'decided_at' => $decidedAt,
                    'created_at' => $decidedAt->copy()->subHours(6),
                    'updated_at' => $decidedAt,
                ];

                if ($status === 'rejected') {
                    $row['reject_reason'] = $this->officerReason($sequence);
                }

                if ($status === 'cancelled_by_user') {
                    $row['cancel_reason'] = $this->userReason($sequence);
                    // Pembatalan oleh pengguna tidak melibatkan petugas.
                    $row['decided_by'] = null;
                }

                if ($status === 'cancelled_by_officer') {
                    $row['cancel_reason'] = $this->officerReason($sequence);
                }

                $reservation = Reservation::create($row);
                $reservation->created_at = $row['created_at'];
                $reservation->updated_at = $row['updated_at'];
                $reservation->saveQuietly();
            }
        }
    }

    /**
     * Batas kedaluwarsa persetujuan.
     *
     * Dua pending dibuat straddling leadTimeCutoff(): satu tepat satu menit
     * sebelumnya harus berubah menjadi cancelled_by_system, dan satu tepat
     * satu menit sesudahnya harus tetap pending. Setelah expireStale()
     * dijalankan, perbedaan kedua baris itulah bukti bahwa batas 60 menit
     * benar-benar diuji dan bukan hanya dibaca di dokumentasi.
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Facility>  $facilities
     */
    private function seedExpiryBoundary(
        ReservationService $service,
        Collection $users,
        Collection $facilities,
        ReservationAvailability $availability,
    ): void {
        $shape = VolumeRoster::slotShape();
        $cutoff = $availability->leadTimeCutoff();

        $boundary = [];

        foreach (['sebelum' => -1, 'sesudah' => 1] as $label => $offset) {
            $start = $cutoff->copy()->addMinutes($offset);
            $facility = $facilities[$offset > 0 ? 1 : 0];
            $user = $users[$offset > 0 ? 1 : 0];

            $boundary[$label] = Reservation::create([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'purpose' => "Batas kedaluwarsa {$label} (skenario volume).",
                'start_time' => $start,
                'end_time' => $start->copy()->addMinutes($shape['minutes'] * 2),
                'status' => 'pending',
            ]);
        }

        $service->expireStale();

        $expired = $boundary['sebelum']->refresh();
        $survivor = $boundary['sesudah']->refresh();

        if ($expired->status !== 'cancelled_by_system') {
            throw new RuntimeException(
                "Pending satu menit sebelum batas harus menjadi cancelled_by_system, terbaca {$expired->status}."
            );
        }

        if ($survivor->status !== 'pending') {
            throw new RuntimeException(
                "Pending satu menit setelah batas harus tetap pending, terbaca {$survivor->status}."
            );
        }

        if ($expired->cancel_reason === null || $expired->decided_at === null) {
            throw new RuntimeException('cancelled_by_system wajib punya cancel_reason dan decided_at.');
        }
    }

    /**
     * Tujuan pemesanan. Panjang bervariasi dari batas bawah sampai tepat
     * batas atas supaya validasi minimum dan maximum ikut teruji, bukan
     * hanya lolos karena semua panjangnya hampir sama.
     */
    private function purpose(int $sequence): string
    {
        if ($sequence % 7 === 0) {
            // Tepat 255 karakter: panjang maksimum yang diizinkan aturan.
            return mb_substr(str_repeat(self::LONG_PURPOSE_SEGMENT, 8), 0, 255);
        }

        if ($sequence % 11 === 0) {
            // Tepat 10 karakter: panjang minimum yang diizinkan aturan.
            return self::MINIMUM_PURPOSE;
        }

        if ($sequence % 3 === 0) {
            return self::SHORT_PURPOSES[$sequence % count(self::SHORT_PURPOSES)];
        }

        return self::PURPOSE_HEADS[$sequence % count(self::PURPOSE_HEADS)]
            .' kegiatan nomor '.$sequence
            .' yang dijadwalkan pemohon untuk kebutuhan perkuliahan internal.';
    }

    /**
     * Alasan penolapan petugas, minimal ReservationService::MIN_OFFICER_REASON.
     */
    private function officerReason(int $sequence): string
    {
        $reasons = [
            'Fasilitas sedang dipakai kegiatan lain yang tidak tercatat pada sistem.',
            'Permohonan beririsan dengan jadwal mata kuliah yang sudah disetujui.',
            'Keperluan khusus memerlukan penyesuaian jadwal melalui koordinasi petugas.',
            'Jam tersebut sudah dialokasikan untuk kegiatan institutional resmi.',
        ];

        return $reasons[$sequence % count($reasons)].' (volume-'.$sequence.')';
    }

    /**
     * Alasan pembatalan oleh pengguna, minimal
     * ReservationService::MIN_USER_CANCEL_REASON.
     */
    private function userReason(int $sequence): string
    {
        $reasons = [
            'Ada perubahan jadwal mendadak sehingga tidak bisa hadir.',
            'Ruangan dibutuhkan untuk keperluan lain yang lebih mendesak.',
            'Kegiatan dibatalkan oleh penyelenggara kegiatan.',
        ];

        return $reasons[$sequence % count($reasons)].' (volume-'.$sequence.')';
    }

    /**
     * Bukti cakupan. Semua status harus muncul, termasuk yang dua status
     * sistem yang hanya bisa lahir dari approve() dan expireStale().
     */
    private function assertCoverage(): void
    {
        $statuses = [
            'pending', 'approved', 'rejected',
            'cancelled_by_user', 'cancelled_by_officer',
            'cancelled_by_system', 'rejected_by_system',
        ];

        foreach ($statuses as $status) {
            if (Reservation::where('status', $status)->doesntExist()) {
                throw new RuntimeException("Tidak ada reservasi berstatus {$status}.");
            }
        }

        if (Reservation::whereColumn('end_time', '<=', 'start_time')->exists()) {
            throw new RuntimeException('Ada reservasi dengan rentang waktu yang tidak valid.');
        }

        $closedWithoutReason = Reservation::whereIn('status', ['rejected', 'cancelled_by_user', 'cancelled_by_officer'])
            ->where(function ($query): void {
                $query->whereNull('reject_reason')->whereNull('cancel_reason');
            })
            ->count();

        if ($closedWithoutReason > 0) {
            throw new RuntimeException("Ada {$closedWithoutReason} reservasi tertutup tanpa alasan.");
        }
    }
}
