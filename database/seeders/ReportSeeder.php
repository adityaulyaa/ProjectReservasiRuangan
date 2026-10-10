<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Report;
use App\Models\ReportLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        if (Report::exists()) {
            return;
        }

        $staff = User::where('role', 'staff')->first();
        $users = User::where('role', 'user')->where('is_verified', true)->get();
        $facilities = Facility::all();

        if ($users->isEmpty() || $facilities->isEmpty() || ! $staff) {
            return;
        }

        $reportsData = [
            [
                'facility' => 'Ruang Kolaborasi Timur',
                'category' => 'ac',
                'description' => 'AC tidak dingin dan mengeluarkan suara berisik. Perlu segera diperbaiki karena mengganggu kegiatan.',
                'status' => 'in_progress',
                'resolution' => null,
                'user_idx' => 0,
            ],
            [
                'facility' => 'Lab Data Science',
                'category' => 'it',
                'description' => 'Beberapa komputer tidak dapat terkoneksi ke jaringan internet. Koneksi terputus-putus.',
                'status' => 'in_progress',
                'resolution' => null,
                'user_idx' => 1,
            ],
            [
                'facility' => 'Ruang Rapat Utama',
                'category' => 'furniture',
                'description' => 'Kursi nomor 5 dan 8 kakinya goyang, tidak stabil saat diduduki. Berbahaya untuk penggunaan jangka panjang.',
                'status' => 'resolved',
                'resolution' => 'Kursi telah diganti dengan yang baru. Kondisi sudah aman untuk digunakan.',
                'user_idx' => 2,
            ],
            [
                'facility' => 'Lab Komputer RPL',
                'category' => 'listrik',
                'description' => 'Lampu di deretan kiri ruangan tidak menyala. Stop kontak nomor 12-15 tidak berfungsi.',
                'status' => 'resolved',
                'resolution' => 'Perbaikan instalasi listrik telah selesai. Semua lampu dan stop kontak berfungsi normal.',
                'user_idx' => 3,
            ],
            [
                'facility' => 'Auditorium Kampus',
                'category' => 'it',
                'description' => 'Proyektor menampilkan gambar buram dan sering mati sendiri. Sistem audio juga mengalami gangguan suara.',
                'status' => 'new',
                'resolution' => null,
                'user_idx' => 4,
            ],
            [
                'facility' => 'Perpustakaan Digital',
                'category' => 'ac',
                'description' => 'Suhu ruangan terlalu panas, AC sepertinya rusak atau kapasitas tidak mencukupi untuk ruangan.',
                'status' => 'new',
                'resolution' => null,
                'user_idx' => 0,
            ],
            [
                'facility' => 'Lab Desain Grafis',
                'category' => 'it',
                'description' => 'Monitor komputer nomor 7 bergaris-garis dan tidak bisa digunakan untuk desain grafis.',
                'status' => 'rejected',
                'resolution' => 'Setelah pengecekan, monitor masih berfungsi normal. Masalah terjadi karena kabel VGA longgar.',
                'user_idx' => 1,
            ],
            [
                'facility' => 'Ruang Presentasi B',
                'category' => 'furniture',
                'description' => 'Meja presenter goyang dan cat whiteboard sudah pudar sehingga sulit dibaca.',
                'status' => 'resolved',
                'resolution' => 'Meja telah diperbaiki dan whiteboard diganti dengan yang baru.',
                'user_idx' => 2,
            ],
            [
                'facility' => 'Studio Audio',
                'category' => 'plumbing',
                'description' => 'Wastafel di studio bocor dan air menetes terus menerus. Lantai menjadi licin dan berbahaya.',
                'status' => 'new',
                'resolution' => null,
                'user_idx' => 3,
            ],
            [
                'facility' => 'Lab Robotika',
                'category' => 'lainnya',
                'description' => 'Pintu masuk sulit dibuka dan sistem ventilasi kurang baik sehingga ruangan pengap.',
                'status' => 'in_progress',
                'resolution' => null,
                'user_idx' => 4,
            ],
        ];

        foreach ($reportsData as $item) {
            $facility = $facilities->firstWhere('name', $item['facility']);
            if (! $facility) {
                continue;
            }

            $user = $users[$item['user_idx'] % $users->count()];
            $processedBy = in_array($item['status'], ['in_progress', 'resolved', 'rejected']) ? $staff->id : null;

            $report = Report::create([
                'user_id' => $user->id,
                'facility_id' => $facility->id,
                'category' => $item['category'],
                'description' => $item['description'],
                'photo_path' => null,
                'status' => $item['status'],
                'resolution_note' => $item['resolution'],
                'processed_by' => $processedBy,
            ]);

            ReportLog::create([
                'report_id' => $report->id,
                'actor_id' => $user->id,
                'action' => 'created',
                'old_status' => null,
                'new_status' => 'new',
                'note' => 'Laporan kerusakan diajukan',
            ]);

            if ($item['status'] === 'in_progress') {
                ReportLog::create([
                    'report_id' => $report->id,
                    'actor_id' => $staff->id,
                    'action' => 'status_changed',
                    'old_status' => 'new',
                    'new_status' => 'in_progress',
                    'note' => 'Laporan sedang ditangani',
                ]);
            } elseif ($item['status'] === 'resolved') {
                ReportLog::create([
                    'report_id' => $report->id,
                    'actor_id' => $staff->id,
                    'action' => 'status_changed',
                    'old_status' => 'new',
                    'new_status' => 'in_progress',
                    'note' => 'Laporan sedang ditangani',
                ]);

                ReportLog::create([
                    'report_id' => $report->id,
                    'actor_id' => $staff->id,
                    'action' => 'resolved',
                    'old_status' => 'in_progress',
                    'new_status' => 'resolved',
                    'note' => $item['resolution'],
                ]);
            } elseif ($item['status'] === 'rejected') {
                ReportLog::create([
                    'report_id' => $report->id,
                    'actor_id' => $staff->id,
                    'action' => 'rejected',
                    'old_status' => 'new',
                    'new_status' => 'rejected',
                    'note' => $item['resolution'],
                ]);
            }
        }
    }
}
