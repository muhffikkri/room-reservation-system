<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Dua laporan contoh untuk demo antrean laporan (§15).
 *
 * Budi melaporkan PC Lab yang rusak (status baru, tanpa foto) dan lampu
 * Ruang Kelas B-201 yang mati (status diproses, dengan foto bukti).
 * Contoh ini mengisi dashboard antrean petugas tanpa data buatan berlebihan.
 */
class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $budi = User::where('email', 'budi@student.kampus.test')->firstOrFail();
        $petugas = User::where('email', 'petugas@kampus.test')->firstOrFail();

        $lab = Facility::where('name', 'Lab Komputer 1')->firstOrFail();
        $kelas = Facility::where('name', 'Ruang Kelas B-201')->firstOrFail();

        Report::firstOrCreate(
            ['user_id' => $budi->id, 'facility_id' => $lab->id, 'category' => 'kerusakan_alat'],
            ['description' => 'Tiga unit PC di deret kedua tidak bisa menyala, kemungkinan PSU rusak.', 'status' => 'baru'],
        );

        $photoPath = null;
        $sourcePath = public_path('images/ruang-kelas.webp');
        if (file_exists($sourcePath)) {
            $dest = 'reports/seed-demo-laporan.webp';
            Storage::disk('local')->put($dest, (string) file_get_contents($sourcePath));
            $photoPath = $dest;
        }

        $kelasReport = Report::firstOrCreate(
            ['user_id' => $budi->id, 'facility_id' => $kelas->id, 'category' => 'listrik'],
            [
                'description' => 'Lampu ruangan mati separuh dan stopkontak depan tidak bertegangan.',
                'status' => 'baru',
                'photo' => $photoPath,
            ],
        );

        // Status diproses ditulis lewat service agar baris audit ikut tercatat (§9.2).
        if ($kelasReport->status === 'baru') {
            app(ReportService::class)->transition(
                $kelasReport,
                $petugas,
                'diproses',
                'Petugas mulai menangani lampu dan stopkontak ruangan.',
            );
        }
    }
}
