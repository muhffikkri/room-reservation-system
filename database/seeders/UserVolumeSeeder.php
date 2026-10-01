<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Volume akun untuk menguji daftar akun, antrean verifikasi, dan batas layout.
 *
 * TUJUHAN
 *
 *  1. Paginasi: admin.pengguna dan admin.pengguna.verifikasi memakai 15
 *     baris per halaman, jadi keduanya harus punya beberapa halaman.
 *  2. Cakupan nilai domain: tiga role x tiga account_status, ditambah akun
 *     yang ditolak lalu dikembalikan ke pending (satu slot audit).
 *  3. Batas layout: nama dari 2 sampai 100 karakter (batas name pada
 *     AdminAccountRequest), nama ber-karakter khusus, dan nomor telepon
 *     yang mengikuti format hasil AccountAttributes::normalizePhone.
 *
 * STATUS LAHIR PER KELOMPOK
 *
 * Hanya akun `pengguna` yang lahir `pending`. AccountVerificationService
 * menolak target yang role-nya bukan `pengguna` (AccountVerificationService::verify),
 * jadi petugas dan admin tidak mungkin lewat jalur itu. Keduanya provisioned
 * institusi dan memang sudah `aktif` sejak lahir, sama seperti akun demo di
 * UserSeeder.
 *
 * Finalisasi akun `pengguna` dilakukan AccountVerificationActionVolumeSeeder
 * lewat AccountVerificationService, sehingga setiap baris
 * account_verification_actions benar-benar berasal dari service, bukan
 * salinan kedua dari aturan BR-14.
 *
 * Prefix identity dikuasai kelas ini dan dibaca seeder verifikasi, jadi
 * tidak ada daftar email akun volume yang ditulis dua kali.
 */
final class UserVolumeSeeder extends Seeder
{
    /**
     * Prefix penanda identity akun volume. Satu-satunya pemilik penanda ini.
     */
    public const IDENTITY_PREFIX = 'VOL-';

    /**
     * Prefix email akun volume.
     */
    public const EMAIL_PREFIX = 'volume-';

    /**
     * Domain email lokal untuk seluruh akun volume.
     */
    public const EMAIL_DOMAIN = 'kampus.test';

    /**
     * Kelompok akun volume, dikelompokkan menurut alur yang akan
     * dijalankan AccountVerificationActionVolumeSeeder: nama kelompok inilah
     * yang menjadi kunci orkestrasi di seeder tersebut.
     *
     * @var array<string, array{role: string, count: int}>
     */
    public const GROUPS = [
        'pengguna' => ['role' => 'pengguna', 'count' => 24],
        'petugas' => ['role' => 'petugas', 'count' => 4],
        'admin' => ['role' => 'admin', 'count' => 2],
        'pending' => ['role' => 'pengguna', 'count' => 30],
        'ditolak' => ['role' => 'pengguna', 'count' => 6],
        'restore' => ['role' => 'pengguna', 'count' => 4],
    ];

    /**
     * Nama cavity yang dipakai ulang, ditambah gelar agar isi kolom nama
     * terasa nyata dan bukan "User 1" sampai "User 70".
     *
     * @var list<string>
     */
    private const NAMES = [
        'Ahmad Fauzi', 'Bunga Lestari', 'Citra Dewi', 'Dimas Prayoga', 'Eka Wulandari',
        'Fajar Nugroho', 'Gita Ayu', 'Hendra Kusuma', 'Indah Permata', 'Joko Santoso',
        'Kartika Sari', 'Lukman Hakim', 'Maya Puspita', 'Nanda Prasetyo', 'Olivia Tampubolon',
        'Panji Ashtar', 'Putri Maharani', 'Rizky Ramadhan', 'Sinta Amelia', 'Taufik Hidayat',
        'Ulfa Zahra', 'Vina Oktaviani', 'Wulan Sari', 'Yoga Pratama',
    ];

    /**
     * Nama dengan karakter khusus. Tabel memakai truncate + title, ekspor
     * CSV dan HTML meng-escape nilai, dan nama ikut masuk PDF rekap, jadi
     * kutip ganda, kutip tunggal, dan tanda hubung panjang menutup jalur
     * yang paling sering salah.
     *
     * @var list<string>
     */
    private const SPECIAL_NAMES = [
        'Bagus "Bebe" Pratama',
        'Rani & Family',
        "Yosua O'Neill",
        'Nur Aini - Pamekasan',
    ];

    /**
     * Nama sepanjang lebih dari 100 karakter, yaitu batas `name` pada
     * AdminAccountRequest dan RegisterController. Dipotong tepat di batas
     * supaya pengaman max:100 ikut teruji, bukan hanya dibaca.
     */
    private const LONG_NAME = 'Dr. Dra. Hj. Siti Maryam binti Abdul Rahman Chowdhury '
        .'Kusumawardhani Al-Mizan Fahruddin Permatasari';

    /**
     * Panjang maksimum nama yang boleh disimpan form akun.
     */
    private const NAME_MAX = 100;

    /**
     * Rentang pendaftaran yang disebar agar urutan
     * `orderBy('created_at')` pada antrean verifikasi punya nilai berbeda.
     * Tanpa ini semua baris berbagi satu timestamp dan urutan halaman bisa
     * berubah antar-jalannya.
     */
    private const REGISTRATION_WINDOW_DAYS = 60;

    /**
     * Hash siap pakai untuk seluruh akun volume.
     *
     * Password di-hash sekali per jalannya seeder, bukan sekali per akun.
     * Cost bcrypt di config ini 12, jadi satu hash memakan sekitar 0,17
     * detik; tujuh puluh akun berarti sekitar dua belas detik untuk data
     * yang isinya justru sama. Cast `hashed` pada model User mengenali
     * hash yang sudah jadi dan tidak menghitungnya dua kali, jadi hasil
     * login tetap identik dengan seeder akun demo.
     */
    private string $passwordHash;

    public function run(): void
    {
        SeedPassword::guardAgainstSharedEnvironments();

        $this->passwordHash = Hash::make(SeedPassword::required(
            SeedPassword::VOLUME_KEY,
            SeedPassword::VOLUME_FALLBACK_KEY,
        ));

        $sequence = 0;

        foreach (self::GROUPS as $group => $spec) {
            for ($index = 1; $index <= $spec['count']; $index++) {
                $sequence++;

                User::firstOrCreate(
                    ['email' => $this->email($group, $index)],
                    $this->attributes($group, $spec['role'], $index, $sequence),
                );
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(string $group, string $role, int $index, int $sequence): array
    {
        $registeredAt = Carbon::now()
            ->subDays(self::REGISTRATION_WINDOW_DAYS - ($sequence % self::REGISTRATION_WINDOW_DAYS))
            ->setTime(7 + ($sequence % 12), ($sequence * 7) % 60, 0);

        return [
            'name' => $this->name($sequence),
            'password' => $this->passwordHash,
            'role' => $role,
            // Hanya akun pengguna yang lewat verifikasi admin. Petugas dan
            // admin memang provisioned institusi, jadi keduanya sudah
            // aktif sejak lahir dan tidak punya jejak audit verifikasi.
            'account_status' => $role === 'pengguna' ? 'pending' : 'aktif',
            'identity' => $this->identity($group, $index),
            'phone' => $this->phone($sequence),
            // Pendaftaran mandiri di aplikasi ini tidak punya langkah
            // verifikasi email, jadi sebagian besar akun kosong di sini.
            'email_verified_at' => $sequence % 5 === 0 ? now() : null,
            'created_at' => $registeredAt,
            'updated_at' => $registeredAt,
        ];
    }

    private function email(string $group, int $index): string
    {
        return self::EMAIL_PREFIX.$group.'-'.$this->suffix($index).'@'.self::EMAIL_DOMAIN;
    }

    private function identity(string $group, int $index): string
    {
        return self::IDENTITY_PREFIX.strtoupper($group).'-'.$this->suffix($index);
    }

    private function suffix(int $index): string
    {
        return str_pad((string) $index, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Nomor telepon selalu mengikuti format hasil AccountAttributes::normalizePhone
     * (+62 diikuti 8 sampai 13 digit) supaya tidak ada baris volume yang
     * mustahil lahir lewat form akun.
     */
    private function phone(int $sequence): string
    {
        return '+628'.str_pad((string) $sequence, 11, '0', STR_PAD_LEFT);
    }

    /**
     * Empat panjang nama supaya navbar, tabel, dan kartu bisa diuji pada
     * kedua ujungnya sekaligus.
     */
    private function name(int $sequence): string
    {
        if ($sequence % 9 === 0) {
            return Str::limit(self::LONG_NAME, self::NAME_MAX, '');
        }

        if ($sequence % 13 === 0) {
            return 'An';
        }

        if ($sequence % 11 === 0) {
            return self::SPECIAL_NAMES[intdiv($sequence, 11) % count(self::SPECIAL_NAMES)];
        }

        $base = self::NAMES[$sequence % count(self::NAMES)];

        return $sequence % 2 === 0 ? 'Dr. '.$base : $base;
    }
}
