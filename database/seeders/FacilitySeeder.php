<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

/**
 * Lima fasilitas contoh untuk demo (§15).
 *
 * Empat berstatus aktif dan satu (Proyektor P-01) berstatus perbaikan
 * sebagai contoh fasilitas yang tidak dapat user pesan (BR-12).
 */
class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Aula Terpadu', 'type' => 'aula', 'location' => 'Gedung A', 'capacity' => 300, 'status' => 'aktif', 'description' => 'Aula serbaguna untuk acara besar.'],
            ['name' => 'Lab Komputer 1', 'type' => 'laboratorium', 'location' => 'Gedung C', 'capacity' => 40, 'status' => 'aktif', 'description' => 'Laboratorium komputer dengan 40 unit PC.'],
            ['name' => 'Ruang Kelas B-201', 'type' => 'ruang_kelas', 'location' => 'Gedung B', 'capacity' => 50, 'status' => 'aktif', 'description' => 'Ruang kelas ber-AC dengan proyektor.'],
            ['name' => 'Lapangan Futsal', 'type' => 'lapangan', 'location' => 'Area Timur', 'capacity' => 20, 'status' => 'aktif', 'description' => 'Lapangan futsal outdoor.'],
            ['name' => 'Proyektor Portable P-01', 'type' => 'alat', 'location' => 'Unit AV', 'capacity' => 1, 'status' => 'perbaikan', 'description' => 'Proyektor portable untuk peminjaman.'],
        ];

        foreach ($facilities as $facility) {
            Facility::firstOrCreate(
                ['name' => $facility['name'], 'location' => $facility['location']],
                $facility,
            );
        }
    }
}
