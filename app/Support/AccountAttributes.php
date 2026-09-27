<?php

namespace App\Support;

/**
 * Normalisasi atribut akun (§7.1).
 *
 * Satu-satunya tempat yang tahu cara membersihkan email dan nomor telepon,
 * dipakai registrasi mandiri dan pembuatan akun oleh admin agar keduanya
 * menyimpan format yang sama (email lowercase, telepon +62...).
 */
final class AccountAttributes
{
    /**
     * Trim lalu lowercase agar User@Kampus.test dan user@kampus.test
     * dianggap email yang sama oleh unique:users,email.
     */
    public static function normalizeEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized === '' ? null : $normalized;
    }

    /**
     * Normalisasi nomor telepon Indonesia ke format +62... (§7.1).
     *
     * Menerima 08..., 62..., atau +62... (spasi/strip diabaikan).
     */
    public static function normalizePhone(mixed $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $digits = preg_replace('/[\s\-().]/', '', trim($phone)) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '+62')) {
            return $digits;
        }

        if (str_starts_with($digits, '62')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Trim penanda identitas (NIM-/NIP-) tanpa mengubah isinya.
     */
    public static function normalizeIdentity(mixed $identity): ?string
    {
        if (! is_string($identity)) {
            return null;
        }

        $normalized = trim($identity);

        return $normalized === '' ? null : $normalized;
    }
}
