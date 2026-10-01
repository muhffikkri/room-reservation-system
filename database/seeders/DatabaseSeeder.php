<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan di bawah dijaga, bukan alphabetical: volume seeder hanya bisa
     * berjalan setelah data yang dibaca VolumeRoster benar-benar ada.
     * Akun lahir lebih dulu supaya verifikasi punya target, lalu fasilitas
     * supaya reservasi dan laporan punya tempat, baru laporan dan
     * pembaruannya, dan terakhir reservasi serta notifikasi yang keduanya
     * butuh facilities dan pengguna yang sudah final.
     *
     * Volume seeder tidak idempoten dan sengaja tidak dibuat idempoten:
     * tujuannya mengisi database kosong setelah migrate:fresh, bukan
     * ditambahkan berulang ke database yang sudah berisi data.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            FacilitySeeder::class,
            ReservationSeeder::class,
            ReportSeeder::class,

            UserVolumeSeeder::class,
            AccountVerificationActionVolumeSeeder::class,
            FacilityVolumeSeeder::class,
            ReportVolumeSeeder::class,
            ReportUpdateVolumeSeeder::class,
            ReservationVolumeSeeder::class,
            NotificationVolumeSeeder::class,
        ]);
    }
}
