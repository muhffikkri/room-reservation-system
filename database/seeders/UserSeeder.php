<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

/**
 * Akun demo untuk presentasi (§5.3).
 *
 * Lima akun ini mencakup semua peran: admin, petugas, Budi (mahasiswa),
 * Sari (dosen), dan satu akun pending sebagai bahan demo verifikasi
 * admin (§14.2 kasus 1). Password dibaca dari environment agar tidak
 * menjadi credential bersama yang tertanam di repository.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('UserSeeder tidak boleh dijalankan di production.');
        }

        $passwords = [
            'admin' => $this->requiredPassword('SEED_ADMIN_PASSWORD'),
            'officer' => $this->requiredPassword('SEED_OFFICER_PASSWORD'),
            'user' => $this->requiredPassword('SEED_USER_PASSWORD'),
            'pending' => $this->requiredPassword('SEED_PENDING_PASSWORD'),
        ];

        User::updateOrCreate(
            ['email' => 'admin@kampus.test'],
            ['name' => 'Admin Kampus', 'password' => $passwords['admin'], 'role' => 'admin', 'account_status' => 'aktif', 'identity' => 'NIP-198001012000', 'phone' => '+628110000001'],
        );

        User::updateOrCreate(
            ['email' => 'petugas@kampus.test'],
            ['name' => 'Petugas Fasilitas', 'password' => $passwords['officer'], 'role' => 'petugas', 'account_status' => 'aktif', 'identity' => 'NIP-199005052000', 'phone' => '+628110000002'],
        );

        User::updateOrCreate(
            ['email' => 'budi@student.kampus.test'],
            ['name' => 'Budi Santoso', 'password' => $passwords['user'], 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '2110512001', 'phone' => '+628120000001'],
        );

        User::updateOrCreate(
            ['email' => 'sari@dosen.kampus.test'],
            ['name' => 'Dr. Sari Rahma', 'password' => $passwords['user'], 'role' => 'pengguna', 'account_status' => 'aktif', 'identity' => '198810102010', 'phone' => '+628120000002'],
        );

        User::updateOrCreate(
            ['email' => 'pending@kampus.test'],
            ['name' => 'Akun Pending', 'password' => $passwords['pending'], 'role' => 'pengguna', 'account_status' => 'pending', 'identity' => '2110512099', 'phone' => '+628120000099'],
        );
    }

    private function requiredPassword(string $environmentKey): string
    {
        $password = env($environmentKey);

        if (! is_string($password) || mb_strlen($password) < 12) {
            throw new LogicException("Environment {$environmentKey} wajib diisi minimal 12 karakter untuk menjalankan seeder.");
        }

        return $password;
    }
}
