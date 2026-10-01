<?php

namespace Database\Seeders;

use LogicException;

/**
 * Satu-satunya pemilik aturan kredensial seeder akun (spec §5.3, §12.1).
 *
 * Dua aturan yang harus berlaku sama untuk setiap seeder akun dan tidak
 * boleh ditulis dua kali:
 *
 *  1. Seeder akun tidak boleh jalan di production atau staging. Akun demo
 *     memakai password yang sudah diketahui orang lain begitu repo ini
 *     publik, sehingga lingkungan bersama tidak boleh mengisinya.
 *  2. Password hanya boleh dibaca dari environment lokal, minimal 12 karakter,
 *     dan tidak pernah ditulis di source code.
 *
 * Menaruh kedua aturan di satu kelas membuat pelonggaran di satu tempat
 * tidak bisa lolos diam-diam di tempat lain.
 */
final class SeedPassword
{
    /**
     * Panjang minimum password seeder (spec §12.1: minimal 12 karakter).
     */
    public const MIN_LENGTH = 12;

    /**
     * Lingkungan yang dilarang menjalankan seeder akun demo.
     *
     * @var list<string>
     */
    public const BLOCKED_ENVIRONMENTS = ['production', 'staging'];

    /**
     * Environment key untuk akun hasil volume seeder.
     *
     * Dipakai sebagai nilai utama, dengan SEED_USER_PASSWORD sebagai cadangan
     * supaya instalasi lama yang belum menyetel key baru tetap bisa seeding.
     */
    public const VOLUME_KEY = 'SEED_VOLUME_PASSWORD';

    /**
     * Environment key cadangan untuk akun hasil volume seeder.
     */
    public const VOLUME_FALLBACK_KEY = 'SEED_USER_PASSWORD';

    /**
     * Halangi seeder akun karena environment-nya dipakai bersama.
     *
     * @throws LogicException
     */
    public static function guardAgainstSharedEnvironments(): void
    {
        if (app()->environment(self::BLOCKED_ENVIRONMENTS)) {
            throw new LogicException(
                'Seeder akun demo tidak boleh dijalankan di '.implode(' atau ', self::BLOCKED_ENVIRONMENTS).'.'
            );
        }
    }

    /**
     * Ambil password seeder dari environment, dengan kunci cadangan opsional.
     *
     * @throws LogicException bila kosong atau terlalu pendek
     */
    public static function required(string $environmentKey, ?string $fallbackKey = null): string
    {
        $password = self::read($environmentKey) ?? ($fallbackKey === null ? null : self::read($fallbackKey));

        if ($password === null || mb_strlen($password) < self::MIN_LENGTH) {
            $hint = $fallbackKey === null
                ? ''
                : " (atau {$fallbackKey} sebagai cadangan)";

            throw new LogicException(
                "Environment {$environmentKey} wajib diisi minimal ".self::MIN_LENGTH." karakter untuk menjalankan seeder{$hint}."
            );
        }

        return $password;
    }

    private static function read(string $environmentKey): ?string
    {
        $value = env($environmentKey);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
