<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $budi = User::where('email', 'budi@student.kampus.test')->firstOrFail();
        $sari = User::where('email', 'sari@dosen.kampus.test')->firstOrFail();
        $admin = User::where('email', 'admin@kampus.test')->firstOrFail();

        $aula = Facility::where('name', 'Aula Terpadu')->firstOrFail();
        $futsal = Facility::where('name', 'Lapangan Futsal')->firstOrFail();

        Reservation::firstOrCreate(
            ['user_id' => $sari->id, 'facility_id' => $aula->id, 'start_time' => now()->tomorrow()->setTime(8, 0)],
            [
                'purpose' => 'Seminar hasil penelitian prodi',
                'end_time' => now()->tomorrow()->setTime(10, 0),
                'status' => 'approved',
                'decided_by' => $admin->id,
                'decided_at' => now(),
            ],
        );

        Reservation::firstOrCreate(
            ['user_id' => $budi->id, 'facility_id' => $futsal->id, 'start_time' => now()->tomorrow()->setTime(16, 0)],
            [
                'purpose' => 'Latihan rutin tim futsal mahasiswa',
                'end_time' => now()->tomorrow()->setTime(18, 0),
                'status' => 'pending',
            ],
        );

        Reservation::firstOrCreate(
            ['user_id' => $budi->id, 'facility_id' => $aula->id, 'start_time' => now()->addDays(2)->setTime(8, 0)],
            [
                'purpose' => 'Rapat panitia wisuda',
                'end_time' => now()->addDays(2)->setTime(9, 30),
                'status' => 'rejected',
                'reject_reason' => 'Fasilitas sudah terpakai untuk kegiatan institusi.',
                'decided_by' => $admin->id,
                'decided_at' => now(),
            ],
        );
    }
}
