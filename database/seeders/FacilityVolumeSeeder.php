<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Volume fasilitas untuk menguji katalog publik, daftar admin, filter, dan
 * batas layout kartu.
 *
 * JUMLAH DAN PEMBAGIAN
 *
 *  - 52 fasilitas dengan lokasi unik. Jumlah ini sengaja di atas
 *    Facility::MAX_PUBLIC_FILTER_OPTIONS (50) supaya scope
 *    publicLocationOptions() benar-benar memotong daftar opsi lokasi,
 *    bukan sekadar kebetulan tidak melewati batas.
 *  - 16 fasilitas di tiga lokasi yang sama. Tiga lokasi dengan banyak hasil
 *    itu yang menguji filter lokasi, yang diuji dengan satu hasil per lokasi
 *    justru tidak menunjukkan apa apa.
 *
 * Status awal setiap fasilitas adalah `aktif` atau `nonaktif`. Status `perbaikan`
 * TIDAK dibuat di sini karena hanya boleh lahir dari FacilityLifecycle
 * markForRepair(), yaitu laporan yang sedang diproses. Laporan dibuat
 * ReportVolumeSeeder dan diproses ReportUpdateVolumeSeeder.
 *
 * Batas layout yang dicakup: panjang nama 4 sampai 120 karakter (batas
 * FacilityRequest), deskripsi null, kosong, satu kata, tepat
 * Facility::MAX_DESCRIPTION_WORDS, dan 15 kata lebih dari batas itu untuk
 * menguji pemotongan short_description.
 */
final class FacilityVolumeSeeder extends Seeder
{
    /**
     * Prefix nama fasilitas volume. Satu-satunya pemilik penanda ini;
     * VolumeRoster memakainya untuk mencari fasilitas hasil volume.
     *
     * Sengaja tanpa spasi di akhir supaya nama fasilitas yang dipotong
     * menjadi empat karakter pun masih diawali prefix ini. Fasilitas demo
     * di FacilitySeeder tidak satu pun diawali "Vol".
     */
    public const NAME_PREFIX = 'Vol';

    /**
     * Jumlah fasilitas dengan lokasi unik, harus melewati
     * Facility::MAX_PUBLIC_FILTER_OPTIONS supaya batas opsi lokasi teruji.
     */
    private const UNIQUE_LOCATION_COUNT = 52;

    /**
     * Lokasi bersama dan jumlah fasilitasnya. Lokasi yang dipakai bersama
     * filter lokasi diuji dengan hasil banyak, bukan hasil satu.
     *
     * @var array<string, int>
     */
    private const SHARED_LOCATIONS = [
        'Gedung Terpadu Lantai 1' => 6,
        'Gedung Teknik Blok C' => 5,
        'Area Sport Timur' => 5,
    ];

    /**
     * Bentuk nama dasar per tipe, memakai nilai yang sudah ada di
     * Facility::TYPE_LABELS supaya daftar tipe tidak ditulis ulang di sini.
     *
     * @var array<string, list<string>>
     */
    private const NAME_BASES = [
        'ruang_kelas' => ['Ruang Kelas', 'Ruang Tutorial', 'Ruang Ujian', 'Ruang Kuliah'],
        'aula' => ['Aula', 'Auditorium', 'Ruang Serbaguna'],
        'laboratorium' => ['Lab', 'Laboratorium', 'Pusat Praktik'],
        'alat' => ['Proyektor', 'Kamera Dokumen', 'Sound System', 'Tripod', 'Printer'],
        'lapangan' => ['Lapangan', 'Gelanggang', 'Halaman'],
    ];

    /**
     * Rentang kapasitas per tipe. Semua nilai di dalam rentang
     * FacilityRequest (1 sampai 100000); batas amin dan batas atasnya
     * ditimpa eksplisit oleh boundaryCapacity().
     *
     * @var array<string, array{int, int}>
     */
    private const CAPACITY_RANGES = [
        'ruang_kelas' => [10, 120],
        'aula' => [100, 500],
        'laboratorium' => [20, 60],
        'alat' => [1, 5],
        'lapangan' => [10, 60],
    ];

    /**
     * Kamus kata untuk menyusun deskripsi dengan jumlah kata yang pasti.
     *
     * @var list<string>
     */
    private const WORDS = [
        'ruangan', 'dipakai', 'untuk', 'kegiatan', 'akademik', 'harian', 'dengan',
        'kapasitas', 'cukup', 'dan', 'ventilasi', 'yang', 'baik', 'serta', 'pencahayaan',
        'memadai', 'sehingga', 'nyaman', 'digunakan', 'oleh', 'mahasiswa', 'dan',
        'dosen', 'dalam', 'menjalankan', 'proses', 'pembelajaran', 'wajah', 'luring',
    ];

    /**
     * Panjang nama tepat di batas FacilityRequest.
     */
    private const NAME_MAX = 120;

    /**
     * Nama paling pendek, untuk menguji truncate di sisi yang lain.
     */
    private const NAME_MIN = 4;

    /**
     * Batas kapasitas bawah dan atas FacilityRequest.
     */
    private const CAPACITY_MIN = 1;

    private const CAPACITY_MAX = 100000;

    public function run(): void
    {
        foreach ($this->specifications() as $index => $spec) {
            Facility::firstOrCreate(
                ['name' => $spec['name'], 'location' => $spec['location']],
                [
                    'type' => $spec['type'],
                    'capacity' => $spec['capacity'],
                    'description' => $spec['description'],
                    'photo' => $spec['photo'],
                    'status' => $spec['status'],
                    // Diisi FacilityLifecycle::markForRepair, bukan di sini.
                    'repair_report_id' => null,
                    'created_at' => $this->createdAt($index),
                    'updated_at' => $this->createdAt($index),
                ],
            );
        }
    }

    /**
     * Denah seluruh fasilitas volume, satu baris per fasilitas.
     *
     * @return list<array{name: string, location: string, type: string, capacity: int, description: ?string, photo: ?string, status: string}>
     */
    private function specifications(): array
    {
        $types = array_keys(Facility::TYPE_LABELS);
        $specs = [];
        $sequence = 0;

        foreach ($this->locations() as $location) {
            $specs[] = $this->spec(++$sequence, $types[$sequence % count($types)], $location);
        }

        return $specs;
    }

    /**
     * Lokasi seluruh fasilitas volume: yang unik dulu, lalu yang dipakai bersama.
     *
     * @return list<string>
     */
    private function locations(): array
    {
        $locations = $this->uniqueLocations();

        foreach (self::SHARED_LOCATIONS as $location => $count) {
            for ($i = 0; $i < $count; $i++) {
                $locations[] = $location;
            }
        }

        return $locations;
    }

    /**
     * Lokasi unik sebanyak UNIQUE_LOCATION_COUNT, dibentuk dari huruf
     * gedung dan nomor lantai supaya tetap terbaca seperti lokasi nyata dan
     * bukan hanya angka urut.
     *
     * Jumlahnya sengaja melewati Facility::MAX_PUBLIC_FILTER_OPTIONS (50),
     * supaya scope publicLocationOptions() benar-benar memotong daftar dan
     * batasnya ikut teruji.
     *
     * @return list<string>
     */
    private function uniqueLocations(): array
    {
        $locations = [];
        $floor = 1;

        while (count($locations) < self::UNIQUE_LOCATION_COUNT) {
            for ($wing = 0; $wing < 2 && count($locations) < self::UNIQUE_LOCATION_COUNT; $wing++) {
                $locations[] = 'Gedung '.chr(65 + $wing).' Lantai '.$floor;
            }

            $floor++;
        }

        return $locations;
    }

    /**
     * Satu baris fasilitas volume.
     *
     * @return array{name: string, location: string, type: string, capacity: int, description: ?string, photo: ?string, status: string}
     */
    private function spec(int $sequence, string $type, string $location): array
    {
        return [
            'name' => $this->name($sequence, $type),
            'location' => $location,
            'type' => $type,
            'capacity' => $this->capacity($sequence, $type),
            'description' => $this->description($sequence),
            'photo' => $this->photo($sequence, $type),
            'status' => $this->status($sequence),
        ];
    }

    /**
     * Nama dengan tiga panjang: 120 karakter (batas FacilityRequest),
     * 4 karakter (sisi pendek truncate), dan nama normal.
     *
     * Ketiganya wajib tetap diawali NAME_PREFIX supaya VolumeRoster dan
     * ReportVolumeSeeder bisa menemukan fasilitas volume, dan wajib tetap
     * unik karena firstOrCreate memakai nama dan lokasi sebagai kuncinya.
     * Nama pendek karena itu memakai digit terakhir, bukan nama yang dipotong,
     * supaya tidak ada baris yang saling menimpa.
     */
    private function name(int $sequence, string $type): string
    {
        // Nama batas atas disusun dari kamus kata, bukan dari nama dasar tipe,
        // karena nama dasar hanya belasan karakter sehingga tidak akan pernah
        // menyentuh batas 120 dan batas form jadi tidak teruji.
        if ($sequence % 12 === 0) {
            return substr(
                self::NAME_PREFIX.$sequence.' '.$this->words(self::NAME_MAX),
                0,
                self::NAME_MAX,
            );
        }

        if ($sequence % 17 === 0) {
            return substr(self::NAME_PREFIX.$sequence, 0, self::NAME_MIN);
        }

        return self::NAME_PREFIX.$sequence.' '.$this->baseName($sequence, $type);
    }

    /**
     * Nama dasar sesuai tipe, memakai kamus di atas yang kuncinya sudah nilai
     * dari Facility::TYPE_LABELS.
     */
    private function baseName(int $sequence, string $type): string
    {
        $bases = self::NAME_BASES[$type] ?? [ucfirst(str_replace('_', ' ', $type))];

        return $bases[$sequence % count($bases)];
    }

    /**
     * Kapasitas per tipe, dengan dua baris batas yang ditimpa eksplisit:
     * kapasitas terkecil dan kapasitas terbesar yang diizinkan form.
     */
    private function capacity(int $sequence, string $type): int
    {
        if ($sequence === 3) {
            return self::CAPACITY_MIN;
        }

        if ($sequence === 9) {
            return self::CAPACITY_MAX;
        }

        [$low, $high] = self::CAPACITY_RANGES[$type];

        return $low + (($sequence * 7) % max(1, $high - $low));
    }

    /**
     * Lima panjang deskripsi: null, string kosong, satu kata, tepat batas
     * kata yang diizinkan, dan 15 kata lebih dari batas itu.
     */
    private function description(int $sequence): ?string
    {
        return match ($sequence % 5) {
            0 => null,
            1 => '',
            2 => 'Standby.',
            3 => $this->words(Facility::MAX_DESCRIPTION_WORDS),
            default => $this->words(Facility::MAX_DESCRIPTION_WORDS + 15),
        };
    }

    /**
     * Susun deskripsi dengan jumlah kata yang tepat, memakai kamus di atas
     * supaya hasilnya deterministik dan tidak berubah antar-jalannya.
     */
    private function words(int $total): string
    {
        $words = [];

        for ($i = 0; $i < $total; $i++) {
            $words[] = self::WORDS[$i % count(self::WORDS)];
        }

        return ucfirst(implode(' ', $words)).'.';
    }

    /**
     * Sebagian besar fasilitas aktif, sebagian nonaktif agar filter
     * dan daftar admin punya lebih dari satu warna status.
     */
    private function status(int $sequence): string
    {
        return $sequence % 5 === 3 ? 'nonaktif' : 'aktif';
    }

    /**
     * Foto pada disk publik untuk sebagian fasilitas. Sisanya sengaja
     * kosong supaya jalur fallback Facility::TYPE_FALLBACK_IMAGES ikut
     * diuji, yaitu saat kartu tidak punya foto unggahan.
     */
    private function photo(int $sequence, string $type): ?string
    {
        if ($sequence % 7 !== 0) {
            return null;
        }

        $sourceName = Facility::TYPE_FALLBACK_IMAGES[$type] ?? null;

        if ($sourceName === null) {
            return null;
        }

        $sourcePath = public_path('images/'.$sourceName);

        if (! file_exists($sourcePath)) {
            throw new RuntimeException('Gambar cadangan fasilitas tidak ditemukan: '.$sourcePath);
        }

        $destination = VolumeRoster::facilityPhotoName($type, intdiv($sequence, 7));

        Storage::disk('public')->put($destination, (string) file_get_contents($sourcePath));

        return $destination;
    }

    /**
     * Waktu pencatatan fasilitas disebar dalam 200 hari supaya daftar
     * admin tidak menampilkan semua baris dengan satu timestamp yang sama.
     */
    private function createdAt(int $sequence): string
    {
        return now()->subDays(200 - ($sequence * 13) % 200)->setTime(8 + ($sequence % 10), 0, 0)->toDateTimeString();
    }
}
