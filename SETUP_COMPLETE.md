# Setup Complete - Sistem Reservasi & Pelaporan Fasilitas Kampus

## Status Final

Project Laravel untuk Sistem Reservasi & Pelaporan Fasilitas Kampus sudah selesai diimplementasikan sampai SRS-21 dan siap untuk demo/presentasi.

## Tech Stack

- Laravel 13
- PHP 8.5
- Laravel Breeze
- Blade + Tailwind CSS + Alpine.js
- MySQL/MariaDB
- Vite
- PHPUnit
- Laravel Pint
- barryvdh/laravel-dompdf

## Modul Selesai

### Public

- Landing page
- Daftar fasilitas
- Filter fasilitas berdasarkan nama, tipe, lokasi, kapasitas
- Availability fasilitas per slot 30 menit

### Auth

- Registrasi mandiri pengguna
- Login dengan verifikasi admin
- Logout
- Role middleware admin/staff/user
- Proteksi akun belum diverifikasi

### Pengguna

- Dashboard pengguna
- Ajukan reservasi
- Riwayat dan detail reservasi
- Pembatalan reservasi sesuai batas waktu
- Lapor kerusakan fasilitas dengan upload foto
- Riwayat dan detail laporan

### Petugas

- Dashboard petugas
- Antrian reservasi pending/approved
- Approve/reject/cancel reservasi
- Antrian laporan new/in_progress
- Update status laporan
- Tandai fasilitas maintenance/active

### Admin

- Dashboard administrator
- CRUD fasilitas
- Kelola pengguna dan petugas
- Verifikasi/penolakan akun pengguna
- Rekap okupansi dan frekuensi kerusakan
- Export CSV, Excel, dan PDF

## Seeder Demo

Seeder demo tersedia melalui:

```bash
php artisan migrate:fresh --seed
```

Data demo meliputi:

- 1 admin
- 2 petugas
- 8 pengguna
- 20 fasilitas
- 22 reservasi
- 10 laporan kerusakan
- Log reservasi dan laporan

## Akun Demo

Semua akun menggunakan password `password123`.

| Role | Email | Status |
|------|-------|--------|
| Admin | `admin@kampus.ac.id` | Terverifikasi |
| Petugas | `staff1@kampus.ac.id` | Terverifikasi |
| Petugas | `staff2@kampus.ac.id` | Terverifikasi |
| Pengguna | `ahmad.fauzi@student.ac.id` | Terverifikasi |
| Pengguna | `siti.nurhaliza@student.ac.id` | Terverifikasi |
| Pengguna | `budi.santoso@student.ac.id` | Terverifikasi |
| Pengguna | `dewi.lestari@student.ac.id` | Terverifikasi |
| Pengguna | `eko.prasetyo@lecturer.ac.id` | Terverifikasi |
| Pengguna | `rina.wati@student.ac.id` | Menunggu verifikasi |
| Pengguna | `joko.widodo@student.ac.id` | Menunggu verifikasi |
| Pengguna | `kartika.sari@student.ac.id` | Menunggu verifikasi |

## Setup Cepat

```bash
composer run setup
```

Atau manual:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan db:create
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

## Validasi Akhir

Validasi terakhir SRS-21:

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

Hasil validasi:

- PHPUnit: 168 tests passed
- Pint: clean
- Vite build: success, `public/build/manifest.json` tersedia
- Export CSV/Excel/PDF: tervalidasi melalui smoke test

## Route Utama

- `/` public landing page
- `/facilities` katalog fasilitas
- `/dashboard` dashboard pengguna
- `/reservations` modul reservasi pengguna
- `/reports` modul laporan pengguna
- `/staff/dashboard` dashboard petugas
- `/staff/reservations/queue` antrian reservasi petugas
- `/staff/reports/queue` antrian laporan petugas
- `/admin/dashboard` dashboard admin
- `/admin/facilities` master fasilitas
- `/admin/users` kelola pengguna
- `/admin/reports` rekap dan export

## Catatan Presentasi

Project siap digunakan untuk demo UTS. Jalankan `php artisan migrate:fresh --seed` sebelum presentasi agar data demo konsisten.
