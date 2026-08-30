<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@kampus.test'],
            ['name' => 'Admin Kampus', 'password' => 'admin123', 'role' => 'admin', 'account_status' => 'aktif'],
        );

        User::firstOrCreate(
            ['email' => 'petugas@kampus.test'],
            ['name' => 'Petugas Fasilitas', 'password' => 'petugas123', 'role' => 'petugas', 'account_status' => 'aktif'],
        );

        User::firstOrCreate(
            ['email' => 'budi@student.kampus.test'],
            ['name' => 'Budi Santoso', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '2110512001'],
        );

        User::firstOrCreate(
            ['email' => 'sari@dosen.kampus.test'],
            ['name' => 'Dr. Sari Rahma', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '198810102010'],
        );

        User::firstOrCreate(
            ['email' => 'pending@kampus.test'],
            ['name' => 'Akun Pending', 'password' => 'user123', 'role' => 'pengguna', 'account_status' => 'pending', 'identity' => '2110512099'],
        );
    }
}
