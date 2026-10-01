<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\User;
use App\Services\ReservationAvailability;
use Illuminate\Support\Collection;

/**
 * Satu-satunya pintu baca data yang dihasilkan volume seeder.
 *
 * Empat seeder volume membutuhkan hal yang sama: petugas aktif, pengguna
 * aktif, fasilitas aktif, dan kelompok akun volume tertentu. Tanpa kelas
 * ini masing-masing menulis query-nya sendiri, dan begitu satu syarat
 * berubah (misalnya urutan atau syarat status aktif) ada empat tempat yang
 * harus ikut berubah tanpa ada satu pun yang gagal.
 *
 * Yang sengaja tidak ada di sini adalah nilai domain apa pun. Status,
 * kategori, dan tipe dibaca dari pemiliknya di app/Models dan app/Services.
 */
final class VolumeRoster
{
    /**
     * Prefix identity akun volume, dimiliki UserVolumeSeeder.
     */
    public const IDENTITY_PREFIX = UserVolumeSeeder::IDENTITY_PREFIX;

    /**
     * Prefix nama fasilitas volume, dimiliki FacilityVolumeSeeder.
     */
    public const FACILITY_PREFIX = FacilityVolumeSeeder::NAME_PREFIX;

    /**
     * Pengguna aktif hasil volume seeder, dipakai sebagai pemohon reservasi
     * dan pelapor.
     *
     * @return Collection<int, User>
     */
    public static function pengguna(): Collection
    {
        return self::activeOf('pengguna');
    }

    /**
     * Petugas aktif hasil volume seeder, dipakai sebagai penentu status
     * laporan dan reservasi.
     *
     * @return Collection<int, User>
     */
    public static function petugas(): Collection
    {
        return self::activeOf('petugas');
    }

    /**
     * Admin aktif hasil volume seeder.
     *
     * @return Collection<int, User>
     */
    public static function admin(): Collection
    {
        return self::activeOf('admin');
    }

    /**
     * @return Collection<int, User>
     */
    public static function activeOf(string $group): Collection
    {
        // group() sudah mengembalikan Collection, jadi penyaring status
        // dilakukan di memori, bukan dengan where() di level query.
        return self::group($group)
            ->filter(fn (User $user) => $user->isActive())
            ->values();
    }

    /**
     * Satu kelompok akun volume, urut nomor akun, apa pun statusnya.
     *
     * Syarat "aktif" sengaja tidak dibebankan di sini karena seeder
     * verifikasi justru membutuhkan yang masih pending.
     *
     * @return Collection<int, User>
     */
    public static function group(string $group): Collection
    {
        return User::query()
            ->where('identity', 'like', self::IDENTITY_PREFIX.strtoupper($group).'-%')
            ->orderBy('identity')
            ->get();
    }

    /**
     * Fasilitas aktif hasil volume seeder.
     *
     * Fasilitas demo tidak ikut supaya skenario perbaikan dan overlap tidak
     * menyentuh baris yang dipakai bahan demo.
     *
     * @return Collection<int, Facility>
     */
    public static function aktifFacilities(): Collection
    {
        return Facility::query()
            ->where('status', 'aktif')
            ->where('name', 'like', self::FACILITY_PREFIX.'%')
            ->orderBy('name')
            ->get();
    }

    /**
     * Nama berkas foto laporan yang deterministik.
     *
     * `migrate:fresh --seed` sering dijalankan berulang. Tanpa nama yang
     * tetap, setiap seeding menambah berkas baru dan storage menumpuk.
     */
    public static function reportPhotoName(int $sequence): string
    {
        return 'reports/volume-'.str_pad((string) $sequence, 4, '0').'.webp';
    }

    /**
     * Nama berkas foto fasilitas yang deterministik.
     */
    public static function facilityPhotoName(string $type, int $sequence): string
    {
        return 'facilities/volume-'.$type.'-'.str_pad((string) $sequence, 3, '0').'.webp';
    }

    /**
     * Bentuk slot harian, diturunkan dari ReservationAvailability supaya
     * jam operasional, jumlah slot, dan panjang slot tidak pernah ditulis
     * ulang di seeder.
     *
     * @return array{count: int, minutes: int}
     */
    public static function slotShape(): array
    {
        $availability = app(ReservationAvailability::class);
        $count = count($availability->timeOptions()) - 1;

        return [
            'count' => $count,
            'minutes' => intdiv($availability->operationalHoursPerDay() * 60, $count),
        ];
    }
}
