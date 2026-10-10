<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\ReservationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        if (Reservation::exists()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();
        $users = User::where('role', 'user')->where('is_verified', true)->get();
        $facilities = Facility::where('status', 'active')->get();

        if ($users->isEmpty() || $facilities->isEmpty() || ! $staff) {
            return;
        }

        $reservationDate = now()->addDays(5)->format('Y-m-d');
        $nextDate = now()->addDays(10)->format('Y-m-d');
        $futureDate = now()->addDays(20)->format('Y-m-d');

        $data = [
            ['facility' => 'Ruang Rapat Utama', 'date' => $reservationDate, 'start' => '09:00', 'end' => '10:00', 'status' => 'approved', 'user_idx' => 0],
            ['facility' => 'Ruang Rapat Utama', 'date' => $reservationDate, 'start' => '10:00', 'end' => '11:00', 'status' => 'approved', 'user_idx' => 1],
            ['facility' => 'Ruang Rapat Utama', 'date' => $reservationDate, 'start' => '14:00', 'end' => '15:30', 'status' => 'pending', 'user_idx' => 2],

            ['facility' => 'Lab Komputer RPL', 'date' => $reservationDate, 'start' => '07:00', 'end' => '09:00', 'status' => 'approved', 'user_idx' => 0],
            ['facility' => 'Lab Komputer RPL', 'date' => $nextDate, 'start' => '13:00', 'end' => '15:00', 'status' => 'approved', 'user_idx' => 1],

            ['facility' => 'Auditorium Kampus', 'date' => $futureDate, 'start' => '10:00', 'end' => '12:00', 'status' => 'approved', 'user_idx' => 2],

            ['facility' => 'Lab Desain Grafis', 'date' => $reservationDate, 'start' => '08:00', 'end' => '10:00', 'status' => 'approved', 'user_idx' => 3],
            ['facility' => 'Lab Desain Grafis', 'date' => $nextDate, 'start' => '11:00', 'end' => '12:30', 'status' => 'rejected', 'user_idx' => 4],

            ['facility' => 'Lab Robotika', 'date' => $futureDate, 'start' => '15:00', 'end' => '16:30', 'status' => 'approved', 'user_idx' => 0],

            ['facility' => 'Ruang Pengajaran A', 'date' => $reservationDate, 'start' => '13:00', 'end' => '14:30', 'status' => 'approved', 'user_idx' => 1],

            ['facility' => 'Perpustakaan Digital', 'date' => $nextDate, 'start' => '09:00', 'end' => '10:30', 'status' => 'cancelled', 'user_idx' => 2],

            ['facility' => 'Ruang Presentasi B', 'date' => $futureDate, 'start' => '14:00', 'end' => '15:30', 'status' => 'approved', 'user_idx' => 3],

            ['facility' => 'Ruang Studi Kelompok A', 'date' => $reservationDate, 'start' => '16:00', 'end' => '17:30', 'status' => 'pending', 'user_idx' => 4],

            ['facility' => 'Studio Audio', 'date' => $nextDate, 'start' => '10:00', 'end' => '11:00', 'status' => 'approved', 'user_idx' => 0],

            ['facility' => 'Lab Elektronika', 'date' => $futureDate, 'start' => '08:00', 'end' => '10:00', 'status' => 'approved', 'user_idx' => 1],

            ['facility' => 'Ruang Kolaborasi Barat', 'date' => $reservationDate, 'start' => '11:00', 'end' => '12:30', 'status' => 'cancelled', 'user_idx' => 2],

            ['facility' => 'Ruang Meeting Informal', 'date' => $nextDate, 'start' => '15:00', 'end' => '16:00', 'status' => 'approved', 'user_idx' => 3],

            ['facility' => 'Ruang Pengajaran B', 'date' => $futureDate, 'start' => '11:00', 'end' => '12:30', 'status' => 'pending', 'user_idx' => 4],

            ['facility' => 'Ruang Pengajaran C', 'date' => $reservationDate, 'start' => '15:30', 'end' => '17:00', 'status' => 'approved', 'user_idx' => 0],

            ['facility' => 'Ruang Studi Kelompok B', 'date' => $nextDate, 'start' => '08:00', 'end' => '09:30', 'status' => 'rejected', 'user_idx' => 1],

            ['facility' => 'Ruang Seminar Kecil', 'date' => $futureDate, 'start' => '09:00', 'end' => '11:00', 'status' => 'pending', 'user_idx' => 2],

            ['facility' => 'Ruang Seminar Kecil', 'date' => $reservationDate, 'start' => '10:30', 'end' => '11:30', 'status' => 'approved', 'user_idx' => 3],
        ];

        foreach ($data as $item) {
            $facility = $facilities->firstWhere('name', $item['facility']);
            if (! $facility) {
                continue;
            }

            $user = $users[$item['user_idx'] % $users->count()];
            $processedBy = in_array($item['status'], ['approved', 'rejected', 'cancelled']) ? $staff->id : null;

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'reservation_date' => $item['date'],
                'start_time' => $item['start'],
                'end_time' => $item['end'],
                'purpose' => $this->generatePurpose(),
                'status' => $item['status'],
                'processed_by' => $processedBy,
                'reject_reason' => $item['status'] === 'rejected' ? 'Fasilitas sedang tidak tersedia pada slot tersebut' : null,
                'cancel_reason' => $item['status'] === 'cancelled' ? 'Rencana dibatalkan oleh pemohon' : null,
            ]);

            ReservationLog::create([
                'reservation_id' => $reservation->id,
                'actor_id' => $user->id,
                'action' => 'created',
                'old_status' => null,
                'new_status' => $item['status'],
                'note' => 'Pengajuan reservasi',
            ]);

            if ($item['status'] === 'approved') {
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'actor_id' => $staff->id,
                    'action' => 'approved',
                    'old_status' => 'pending',
                    'new_status' => 'approved',
                    'note' => 'Disetujui oleh petugas',
                ]);
            } elseif ($item['status'] === 'rejected') {
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'actor_id' => $staff->id,
                    'action' => 'rejected',
                    'old_status' => 'pending',
                    'new_status' => 'rejected',
                    'note' => 'Ditolak oleh petugas',
                ]);
            } elseif ($item['status'] === 'cancelled') {
                ReservationLog::create([
                    'reservation_id' => $reservation->id,
                    'actor_id' => $user->id,
                    'action' => 'cancelled',
                    'old_status' => 'approved',
                    'new_status' => 'cancelled',
                    'note' => 'Dibatalkan oleh pemohon',
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
        ];

        return $purposes[array_rand($purposes)];
    }
}
