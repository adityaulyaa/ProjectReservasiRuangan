# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi web untuk mengelola penggunaan fasilitas kampus (ruang kelas, aula, laboratorium, alat, dan lapangan). Pengguna dapat mengecek ketersediaan dan mengajukan reservasi, serta melaporkan kerusakan atau masalah pada fasilitas yang sama.

## Fitur Utama

- **Multi-role System**: Pengunjung (no-auth), Pengguna (mahasiswa/dosen/staf), Petugas, Admin
- **Reservasi Fasilitas**: Slot 30 menit (07.00-20.00), approval workflow, conflict prevention
- **Pelaporan Kerusakan**: Upload foto, status tracking, integrasi dengan status fasilitas
- **Admin Dashboard**: CRUD fasilitas, user management, export rekap (CSV/Excel/PDF)
- **Real-time Availability**: Kalender ketersediaan per fasilitas

## Tech Stack

- **Framework**: Laravel 13
- **Database**: MySQL 8.0+
- **Frontend**: Blade + Tailwind CSS
- **Authentication**: Laravel Breeze
- **PHP**: 8.5+
- **Node.js**: 18+

## Requirement untuk Development

### Windows Setup (Recommended)

1. **PHP 8.5+**
   - Gunakan Laravel Herd atau Laragon untuk kemudahan
   - Alternative: Install manual dari php.new

2. **Composer 2.x**
   ```bash
   composer --version
   ```

3. **MySQL 8.0+**
   - Include dengan Laravel Herd/Laragon
   - Alternative: Install XAMPP/manual

4. **Node.js 18+**
   ```bash
   node --version
   npm --version
   ```

### Verify Installation

```bash
php -v
composer --version
node --version
mysql --version
```

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/your-team/ProjectReservasi.git
cd ProjectReservasi
```

### 2. Setup Automatico (Recommended)

Perintah `composer run setup` membuat `.env`, key, database otomatis, migrate, dan build assets:

```bash
composer run setup
```

Alternatif manual:

```bash
composer install
npm install
```

### 3. Setup Environment

Copy `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 4. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reservasi_ruangan
DB_USERNAME=root
DB_PASSWORD=
```

Database dibuat otomatis via command `db:create` (idempotent):

```bash
php artisan db:create
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate:fresh --seed
```

### 6. Build Frontend Assets

```bash
npm run build
```

Untuk development dengan hot reload:

```bash
npm run dev
```

Di terminal lain:

```bash
php artisan serve
```

Access aplikasi di `http://localhost:8000`

## Struktur Folder

```
ProjectReservasi/
├── app/
│   ├── Enums/                 # Status & Role enums
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Admin controllers
│   │   │   ├── Staff/         # Staff controllers
│   │   │   ├── User/          # User controllers
│   │   │   └── PublicController.php
│   │   ├── Middleware/        # RoleMiddleware
│   │   └── Requests/          # Form validation
│   ├── Models/                # Eloquent models
│   └── Services/              # Business logic
├── database/
│   ├── migrations/            # Database schemas
│   ├── seeders/               # Demo data
│   └── factories/             # Model factories
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Tailwind CSS
│   └── js/                    # JavaScript
├── routes/
│   ├── web.php                # Public & user routes
│   ├── admin.php              # Admin routes
│   └── staff.php              # Staff routes
├── storage/app/public/reports # Uploaded report photos
├── docs/
│   ├── DATABASE.md            # Database schema
│   ├── API.md                 # Route list
│   └── DEPLOYMENT.md          # Deployment guide
└── tests/                     # PHPUnit tests
```

## User Roles & Access

| Role | Access | Fitur Utama |
|------|--------|-----------|
| Pengunjung | Public (no login) | Lihat daftar fasilitas & ketersediaan |
| Pengguna | After login | Reservasi, lapor kerusakan, lihat history |
| Petugas | /staff | Proses reservasi & laporan, update status fasilitas |
| Admin | /admin | Manage fasilitas, user, export rekap |

## Jam Operasional

- **Hours**: 07.00 - 20.00 WIB
- **Slot Duration**: 30 menit
- **Contoh**: 07.00-07.30, 07.30-08.00, dst.

Validasi slot dilakukan di server-side, bukan hanya di UI.

## Testing

Run PHPUnit tests:

```bash
php artisan test
```

Run dengan coverage:

```bash
php artisan test --coverage
```

## Code Quality

Run PHP Linter (Pint):

```bash
./vendor/bin/pint
```

## Documentation

- `COLLABORATION.md` - Git workflow & development guidelines
- `docs/DATABASE.md` - ERD & database schema
- `docs/API.md` - Route list & endpoint documentation
- `docs/DEPLOYMENT.md` - Production deployment checklist

## Kontribusi Team

Lihat `COLLABORATION.md` untuk:
- Git branch naming convention
- Commit message format
- Pull request process
- Code review checklist

## Support & Issues

Untuk pertanyaan atau issues, silakan buat issue di GitHub dengan format:
- **Title**: [CATEGORY] Brief description
- **Category**: Bug / Feature / Documentation / Question
- **Description**: Detail lengkap dengan steps to reproduce

## Timeline

- **Start Date**: Semester baru 2026
- **Deadline**: 11 Oktober 2026, 12:00 WIB
- **Presentation**: TBA
- **Team Size**: 4-5 mahasiswa
