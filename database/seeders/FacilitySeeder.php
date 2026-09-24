<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name' => 'Ruang Rapat Utama',
                'type' => 'Meeting Room',
                'location' => 'Gedung A Lt.3',
                'capacity' => 20,
                'description' => 'Ruang rapat dengan AC dan whiteboard lengkap, dilengkapi proyektor HD',
                'status' => 'active',
                'image_url' => 'https://go-work-web.storage.googleapis.com/post/492/thumbnail/WORKING-SPACE-MEDAN2.jpg',
            ],
            [
                'name' => 'Ruang Studi Kelompok A',
                'type' => 'Study Room',
                'location' => 'Gedung A Lt.2',
                'capacity' => 8,
                'description' => 'Ruang studi nyaman dengan meja dan kursi ergonomis, cocok untuk diskusi kelompok',
                'status' => 'active',
                'image_url' => 'https://bk.uad.ac.id/wp-content/uploads/25.jpg',
            ],
            [
                'name' => 'Lab Komputer RPL',
                'type' => 'Laboratory',
                'location' => 'Gedung B Lt.1',
                'capacity' => 30,
                'description' => 'Laboratorium programming dengan 30 unit komputer i7 dan software development lengkap',
                'status' => 'active',
                'image_url' => 'https://www.cahayamustikainternesia.com/wp-content/uploads/2026/01/Untitled-design-9.webp',
            ],
            [
                'name' => 'Ruang Kolaborasi Timur',
                'type' => 'Collaboration Space',
                'location' => 'Gedung A Lt.4',
                'capacity' => 15,
                'description' => 'Open space untuk brainstorming dan diskusi terbuka dengan papan kanvas dinding',
                'status' => 'maintenance',
                'image_url' => 'https://png.pngtree.com/thumb_back/fw800/background/20260711/pngtree-modern-open-plan-office-with-collaborative-spaces-image_22021524.webp',
            ],
            [
                'name' => 'Auditorium Kampus',
                'type' => 'Auditorium',
                'location' => 'Gedung C Lt.Ground',
                'capacity' => 150,
                'description' => 'Auditorium dengan kapasitas 150 orang, proyektor 4K dan sistem audio profesional',
                'status' => 'active',
                'image_url' => 'https://schmidt-arch.com/wp-content/uploads/2016/06/lakecentralhs13.jpg',
            ],
            [
                'name' => 'Lab Desain Grafis',
                'type' => 'Laboratory',
                'location' => 'Gedung B Lt.3',
                'capacity' => 25,
                'description' => 'Lab desain dengan workstation dual monitor dan GPU dedicated untuk rendering',
                'status' => 'active',
                'image_url' => 'https://www.unpas.ac.id/wp-content/uploads/2021/11/lab-multimedia-ilkom-unpas-1024x682.jpeg',
            ],
            [
                'name' => 'Ruang Presentasi B',
                'type' => 'Meeting Room',
                'location' => 'Gedung A Lt.2',
                'capacity' => 12,
                'description' => 'Ruang presentasi dengan layar sentuh interaktif 65 inch dan whiteboard digital',
                'status' => 'active',
                'image_url' => 'https://rpmdesigninterior.co.id/wp-content/uploads/2026/02/Tren-Desain-Ruang-Meeting-Minimalis-yang-Elegan.webp',
            ],
            [
                'name' => 'Perpustakaan Digital',
                'type' => 'Study Room',
                'location' => 'Gedung C Lt.2',
                'capacity' => 40,
                'description' => 'Area baca digital dengan WiFi 6 dan power outlet di setiap posisi',
                'status' => 'active',
                'image_url' => 'https://unair.ac.id/wp-content/uploads/2021/01/Ilustrasi-oleh-DPK-Banten.jpg',
            ],
            [
                'name' => 'Lab Robotika',
                'type' => 'Laboratory',
                'location' => 'Gedung B Lt.2',
                'capacity' => 20,
                'description' => 'Laboratorium robotika dengan equipment modern dan area testing robotik lengkap',
                'status' => 'active',
                'image_url' => 'https://mf.b37mrtl.ru/rbthmedia/images/2019.06/original/5d005e8685600a25bb2cdfec.jpg',
            ],
            [
                'name' => 'Ruang Seminar Kecil',
                'type' => 'Meeting Room',
                'location' => 'Gedung C Lt.1',
                'capacity' => 10,
                'description' => 'Ruang seminar intim dengan AC dan sound system berkualitas tinggi',
                'status' => 'active',
                'image_url' => 'https://suterahall.com/wp-content/uploads/2024/10/Sewa-ruang-seminar.jpg',
            ],
            [
                'name' => 'Ruang Studi Kelompok B',
                'type' => 'Study Room',
                'location' => 'Gedung B Lt.Ground',
                'capacity' => 8,
                'description' => 'Ruang studi nyaman lantai dasar, mudah akses dengan pencahayaan optimal',
                'status' => 'active',
                'image_url' => 'https://files.planet.ung.ac.id/fak/27/whatsapp-image-2026-01-14-at-134643-19.01.2026.16.07.40.jpg',
            ],
            [
                'name' => 'Lab Data Science',
                'type' => 'Laboratory',
                'location' => 'Gedung B Lt.4',
                'capacity' => 25,
                'description' => 'Lab data science dengan server processing tinggi dan software analytics terkini',
                'status' => 'maintenance',
                'image_url' => 'https://labworld.in/wp-content/uploads/2024/05/data-science-lab-1.jpg',
            ],
            [
                'name' => 'Ruang Kolaborasi Barat',
                'type' => 'Collaboration Space',
                'location' => 'Gedung C Lt.3',
                'capacity' => 18,
                'description' => 'Ruang kolaborasi dengan kanvas dinding untuk menulis ide dan moodboard',
                'status' => 'active',
                'image_url' => 'https://png.pngtree.com/thumb_back/fh260/background/20260711/pngtree-modern-corporate-meeting-room-with-professionals-collaboration-image_22021445.webp',
            ],
            [
                'name' => 'Ruang Pengajaran A',
                'type' => 'Classroom',
                'location' => 'Gedung A Lt.1',
                'capacity' => 40,
                'description' => 'Kelas standar dengan proyektor dan sound system untuk pembelajaran interaktif',
                'status' => 'active',
                'image_url' => 'https://fia.ui.ac.id/wp-content/uploads/sites/346/2020/07/WhatsApp-Image-2020-07-13-at-09.56.40.jpeg',
            ],
            [
                'name' => 'Ruang Pengajaran B',
                'type' => 'Classroom',
                'location' => 'Gedung A Lt.1',
                'capacity' => 40,
                'description' => 'Kelas standar dengan papan tulis digital dan sistem manajemen pembelajaran',
                'status' => 'active',
                'image_url' => 'https://sci-ui.id/wp-content/uploads/2024/09/20240917_185029-scaled.jpg',
            ],
            [
                'name' => 'Studio Audio',
                'type' => 'Laboratory',
                'location' => 'Gedung D Lt.1',
                'capacity' => 6,
                'description' => 'Studio rekaman audio profesional dengan soundproof dan equipment recording premium',
                'status' => 'active',
                'image_url' => 'https://www.melodiamusik.com/wp-content/uploads/2025/11/thumbnail-small-package.jpg',
            ],
            [
                'name' => 'Ruang Studi Kelompok C',
                'type' => 'Study Room',
                'location' => 'Gedung C Lt.Ground',
                'capacity' => 8,
                'description' => 'Ruang studi dengan pencahayaan optimal dan dekorasi modern untuk fokus belajar',
                'status' => 'active',
                'image_url' => 'https://png.pngtree.com/thumb_back/fh260/background/20250518/pngtree-group-study-session-in-a-modern-classroom-image_17235748.jpg',
            ],
            [
                'name' => 'Ruang Meeting Informal',
                'type' => 'Meeting Room',
                'location' => 'Gedung B Lt.3',
                'capacity' => 6,
                'description' => 'Ruang santai untuk diskusi informal dengan sofa nyaman dan kopi gratis',
                'status' => 'active',
                'image_url' => 'https://verandahotels.com/pakubuwono/wp-content/uploads/2025/02/haro-meeting-pakubuwono-scaled.webp',
            ],
            [
                'name' => 'Lab Elektronika',
                'type' => 'Laboratory',
                'location' => 'Gedung D Lt.2',
                'capacity' => 20,
                'description' => 'Lab elektronika dengan equipment testing lengkap dan prototyping tools modern',
                'status' => 'active',
                'image_url' => 'https://st.depositphotos.com/1781556/1369/i/450/depositphotos_13697514-stock-photo-control-panels-in-an-electronics.jpg',
            ],
            [
                'name' => 'Ruang Pengajaran C',
                'type' => 'Classroom',
                'location' => 'Gedung B Lt.1',
                'capacity' => 35,
                'description' => 'Kelas dengan WiFi 6 dan smart board untuk pembelajaran berbasis teknologi',
                'status' => 'active',
                'image_url' => 'https://png.pngtree.com/thumb_back/fw800/background/20250816/pngtree-modern-classroom-interior-with-wooden-desks-image_18067676.webp',
            ],
        ];

        foreach ($facilities as $facility) {
            Facility::create($facility);
        }
    }
}
