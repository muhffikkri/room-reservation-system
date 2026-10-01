<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Volume laporan kerusakan untuk menguji daftar laporan pengguna, antrean
 * petugas, rekap kategori, batas kuota harian, dan tata letak kartu yang
 * panjang deskripsinya berbeda-beda.
 *
 * SEEDER INI HANYA MELAHIRKAN BARIS `baru`.
 *
 * Semua perpindahan status dilakukan ReportUpdateVolumeSeeder. Pemisahan itu
 * bukan pemecahan file tanpa alasan: `transition()` mengunci baris laporan,
 * mewajibkan petugas aktif, dan menulis satu baris ReportUpdate per langkah.
 * Kalau transisi ikut dicampur di sini, urutan pemrosesan laporan lama dan
 * facility lifecycle (perbaikan) jadi sulit dibaca dan hampir pasti tidak
 * bisa diulang.
 *
 * JUMLAH DAN PEMBAGIAN
 *
 *  - 72 laporan. Daftar laporan pengguna dan petugas menampilkan 10 baris
 *    per halaman, jadi ini kira-kira tujuh halaman dan bukan satu.
 *  - Satu pengguna tepat ReportService::DAILY_REPORT_QUOTA (20) laporan pada
 *    hari ini. Dengan begitu batas kuota ikut teruji tanpa perlu membuat
 *    laporan ke-21 yang memang tidak boleh ada.
 *  - 52 laporan lainnya disebar di 35 hari ke belakang, dengan pelapor
 *    berputar sehingga tidak ada satu pun pengguna yang mendekati kuota.
 *  - Kelima Report::CATEGORIES muncul, dan foto ada pada setiap laporan
 *    ketiga supaya jalur Storage::disk('local') ikut teruji.
 *
 * Fasilitas yang dilaporkan diambil dari semua fasilitas volume, termasuk
 * yang nonaktif, karena melapor kerusakan pada fasilitas nonaktif adalah
 * kasus nyata dan harus tampil di daftar.
 *
 * Syarat database kosong tetap berlaku, sama seperti facility volume:
 * `migrate:fresh --seed`.
 */
final class ReportVolumeSeeder extends Seeder
{
    /**
     * Gambar sumber yang dipakai sebagai foto bukti, sama seperti yang
     * dipakai ReportSeeder.
     */
    private const SOURCE_PHOTO = 'images/ruang-kelas.webp';

    /**
     * Jumlah laporan total. Dipakai ReportUpdateVolumeSeeder sebagai acuan
     * mana yang sudah pada hari ini dan mana yang riwayat.
     */
    public const TOTAL = 72;

    /**
     * Jumlah laporan hari ini untuk satu pengguna, dibatasi tepat pada
     * ReportService::DAILY_REPORT_QUOTA supaya batasnya teruji tanpa
     * melanggar batas itu.
     */
    private const TODAY_PER_FULL_USER = ReportService::DAILY_REPORT_QUOTA;

    /**
     * Sebar laporan riwayat selama 35 hari ke belakang.
     */
    private const HISTORY_DAYS = 35;

    /**
     * Pool deskripsi dengan panjang berbeda. Ringkas, normal, dan panjang;
     * kartu laporan memotong teks sehingga perbedaan panjang inilah yang
     * membuat tinggi baris daftar tidak seragam.
     *
     * @var list<string>
     */
    private const DESCRIPTIONS = [
        'Tidak menyala sejak pagi.',
        'Lampu berkedip terus sejak kemarin sore dan akhirnya mati.',
        'Lantai meluap dan licin. Sudah diberi tanda peringatan, tetapi belum ada pembersihan.',
        'AC menyala tetapi udara yang dikeluarkan tetap hangat. Sudah dicoba di beberapa tombol, hasilnya sama saja.',
        'Pintu yang bermasalah ada di ujung barat, engselnya sudah berkarat.',
        'Stopkontak di dekat pintu tidak bertegangan.',
        'Kursi di deret belakang patah dan sekarang tidak dipakai karena berbahaya.',
    ];

    public function run(): void
    {
        $reporters = VolumeRoster::pengguna();
        $facilities = $this->volumeFacilities();

        if ($reporters->isEmpty() || $facilities->isEmpty()) {
            throw new RuntimeException(
                'UserVolumeSeeder dan FacilityVolumeSeeder harus dijalankan sebelum ReportVolumeSeeder.'
            );
        }

        $this->assertSourcePhotoExists();

        $categories = array_keys(Report::CATEGORIES);

        // Penggolong hari ini: satu pengguna tepat di batas kuota.
        $todayReporters = $reporters->take(1);
        $sequence = 0;

        for ($i = 0; $i < self::TODAY_PER_FULL_USER; $i++) {
            $sequence++;
            $this->createReport(
                $todayReporters[$sequence % $todayReporters->count()],
                $facilities[$sequence % $facilities->count()],
                $categories[$sequence % count($categories)],
                $this->createdAtToday($sequence),
                $sequence,
            );
        }

        // Penggolong riwayat: pelapor berputar tanpa pernah satu pengguna
        // yang punya lebih dari dua laporan pada hari yang sama.
        $historyCount = self::TOTAL - self::TODAY_PER_FULL_USER;
        $historyReporters = $reporters->slice(1)->values();

        for ($i = 0; $i < $historyCount; $i++) {
            $sequence++;
            $reporter = $historyReporters[$i % $historyReporters->count()];

            $this->createReport(
                $reporter,
                $facilities[$sequence % $facilities->count()],
                $categories[$sequence % count($categories)],
                $this->createdAtInHistory($i, $sequence),
                $sequence,
            );
        }
    }

    /**
     * Satu baris laporan `baru` dengan foto mungkin, lalu tanggal dibuatnya
     * diturunkan ke waktu yang benar-benar diminta.
     */
    private function createReport(
        User $reporter,
        Facility $facility,
        string $category,
        Carbon $createdAt,
        int $sequence,
    ): Report {
        $report = Report::create([
            'user_id' => $reporter->id,
            'facility_id' => $facility->id,
            'category' => $category,
            'description' => $this->description($sequence),
            'photo' => $this->photo($sequence),
            'status' => 'baru',
        ]);

        // created_at tidak termasuk Fillable Report, jadi harus diisi
        // eksplisit. saveQuietly dipakai supaya updated_at tidak dihitung
        // ulang di atas nilai yang baru saja ditetapkan.
        $report->created_at = $createdAt;
        $report->updated_at = $createdAt;
        $report->saveQuietly();

        return $report;
    }

    /**
     * Fasilitas volume, aktif dan nonaktif, urut nama.
     *
     * @return Collection<int, Facility>
     */
    private function volumeFacilities(): Collection
    {
        return Facility::query()
            ->where('name', 'like', VolumeRoster::FACILITY_PREFIX.'%')
            ->orderBy('name')
            ->get();
    }

    /**
     * Foto pada setiap laporan ketiga, dengan nama berkas tetap supaya
     * menjalankan seed berulang tidak menambah berkas baru setiap kali.
     */
    private function photo(int $sequence): ?string
    {
        if ($sequence % 3 !== 0) {
            return null;
        }

        $destination = VolumeRoster::reportPhotoName($sequence);
        Storage::disk('local')->put($destination, $this->sourcePhotoBytes());

        return $destination;
    }

    private function description(int $sequence): string
    {
        return self::DESCRIPTIONS[$sequence % count(self::DESCRIPTIONS)];
    }

    /**
     * Laporan hari ini tersebar di jam kerja, satu slot berbeda per laporan
     * supaya urutannya jelas saat diurutkan created_at.
     *
     * Slot yang jatuh di masa depan dijepit ke now(). Tanpa itu, seeding
     * yang dijalankan sore hari akan menghasilkan beberapa laporan dengan
     * created_at besok, dan daftar pengguna akan menampilkan laporan yang
     * belum pernah dibuat.
     */
    private function createdAtToday(int $sequence): Carbon
    {
        $slot = now()->setTime(7 + ($sequence % 12), ($sequence * 3) % 60, 0);

        return $slot->isFuture() ? now()->copy() : $slot;
    }

    /**
     * Riwayat tersebar 35 hari ke belakang. Nilai diturunkan dari indeks,
     * bukan acak, supaya menjalankan seed dua kali menghasilkan data yang
     * sama persis.
     */
    private function createdAtInHistory(int $index, int $sequence): Carbon
    {
        return now()
            ->subDays(1 + ($index * 7) % self::HISTORY_DAYS)
            ->setTime(8 + ($sequence % 10), ($sequence * 11) % 60, 0);
    }

    private function sourcePhotoBytes(): string
    {
        return (string) file_get_contents(public_path(self::SOURCE_PHOTO));
    }

    private function assertSourcePhotoExists(): void
    {
        $source = public_path(self::SOURCE_PHOTO);

        if (! file_exists($source)) {
            throw new RuntimeException('Gambar sumber foto laporan tidak ditemukan: '.$source);
        }
    }
}
