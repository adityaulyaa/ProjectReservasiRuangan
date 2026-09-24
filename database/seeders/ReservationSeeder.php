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
        $facilities = Facility::all()->keyBy('id');
        $users = User::where('role', 'user')->where('is_verified', true)->get();

        if ($users->isEmpty() || $facilities->isEmpty()) {
            return;
        }

        $topFacilities = [
            'Ruang Rapat Utama' => 18,
            'Lab Komputer RPL' => 16,
            'Auditorium Kampus' => 14,
            'Lab Desain Grafis' => 12,
            'Lab Robotika' => 12,
            'Ruang Pengajaran A' => 11,
        ];

        $userIndex = 0;

        foreach ($topFacilities as $facilityName => $targetApproved) {
            $facilityId = $facilities->firstWhere('name', $facilityName)?->id;

            if ($facilityId === null) {
                continue;
            }

            for ($i = 0; $i < $targetApproved; $i++) {
                $user = $users[$userIndex % $users->count()];
                $userIndex++;

                $reservationDate = now()->addDays(rand(2, 60))->format('Y-m-d');
                $startHour = rand(7, 18);
                $startMinute = rand(0, 1) * 30;
                $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
                $endTime = sprintf('%02d:%02d:00', $startHour + 1, $startMinute);

                Reservation::create([
                    'user_id' => $user->id,
                    'facility_id' => $facilityId,
                    'reservation_date' => $reservationDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'purpose' => $this->generatePurpose(),
                    'status' => 'approved',
                    'processed_by' => 1,
                ]);

            }
        }

        $topFacilityIds = $facilities->whereIn('name', array_keys($topFacilities))->keys();
        $otherFacilities = $facilities->keys()->diff($topFacilityIds)->values();

        foreach ($otherFacilities as $facilityId) {
            $targetReservations = rand(2, 5);

            for ($i = 0; $i < $targetReservations; $i++) {
                $user = $users[$userIndex % $users->count()];
                $userIndex++;

                $reservationDate = now()->addDays(rand(2, 60))->format('Y-m-d');
                $startHour = rand(7, 18);
                $startMinute = rand(0, 1) * 30;
                $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
                $endTime = sprintf('%02d:%02d:00', $startHour + 1, $startMinute);

                $status = ['approved', 'approved', 'approved', 'pending', 'rejected', 'cancelled'][rand(0, 5)];
                $processedBy = ($status !== 'pending') ? 1 : null;

                Reservation::create([
                    'user_id' => $user->id,
                    'facility_id' => $facilityId,
                    'reservation_date' => $reservationDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'purpose' => $this->generatePurpose(),
                    'status' => $status,
                    'processed_by' => $processedBy,
                ]);
            }
        }
    }

    private function generatePurpose(): string
    {
        $purposes = [
            'Rapat tim project',
            'Diskusi kelompok tugas',
            'Presentasi materi kuliah',
            'Workshop teknologi',
            'Kegiatan organisasi mahasiswa',
            'Ujian/Quiz',
            'Brainstorming ide',
            'Sesi bimbingan',
            'Pertemuan dosen',
            'Acara seminar',
            'Latihan presentasi',
            'Diskusi skripsi',
            'Workshop web development',
            'Kegiatan pelatihan',
            'Rapat koordinasi',
        ];

        return $purposes[array_rand($purposes)];
    }
}
