<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Database\Seeder;

/**
 * Dua laporan contoh untuk demo antrean laporan (§15).
 *
 * Budi melaporkan PC Lab yang rusak (status baru) dan lampu Ruang Kelas
 * B-201 yang mati (status diproses oleh petugas). Contoh ini mengisi
 * dashboard antrean petugas tanpa data buatan yang berlebihan.
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

        $kelasReport = Report::firstOrCreate(
            ['user_id' => $budi->id, 'facility_id' => $kelas->id, 'category' => 'listrik'],
            ['description' => 'Lampu ruangan mati separuh dan stopkontak depan tidak bertegangan.', 'status' => 'baru'],
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
