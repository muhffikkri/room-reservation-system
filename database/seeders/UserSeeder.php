<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun demo untuk presentasi (§5.3).
 *
 * Lima akun ini mencakup semua peran: admin, petugas, Budi (mahasiswa),
 * Sari (dosen), dan satu akun pending sebagai bahan demo verifikasi
 * admin (§14.2 kasus 1). Password ter-hash otomatis oleh cast hashed
 * di model User.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@kampus.test'],
            ['name' => 'Admin Kampus', 'password' => 'admin123', 'role' => 'admin', 'account_status' => 'aktif', 'identity' => 'NIP-198001012000', 'phone' => '+628110000001'],
        );

        User::firstOrCreate(
            ['email' => 'petugas@kampus.test'],
            ['name' => 'Petugas Fasilitas', 'password' => 'petugas123', 'role' => 'petugas', 'account_status' => 'aktif', 'identity' => 'NIP-199005052000', 'phone' => '+628110000002'],
        );

        User::firstOrCreate(
            ['email' => 'budi@student.kampus.test'],
            ['name' => 'Budi Santoso', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '2110512001', 'phone' => '+628120000001'],
        );

        User::firstOrCreate(
            ['email' => 'sari@dosen.kampus.test'],
            ['name' => 'Dr. Sari Rahma', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '198810102010', 'phone' => '+628120000002'],
        );

        User::firstOrCreate(
            ['email' => 'pending@kampus.test'],
            ['name' => 'Akun Pending', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'pending', 'identity' => '2110512099', 'phone' => '+628120000099'],
        );
    }
}
