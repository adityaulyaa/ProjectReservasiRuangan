# IMPLEMENTATION TASKLIST — Sistem Reservasi & Pelaporan Fasilitas Kampus

Dokumen ini adalah panduan implementasi lengkap untuk proyek Sistem Reservasi & Pelaporan Fasilitas Kampus (deadline 11 Oktober 2026, 12.00 WIB). Setiap SRS ditulis sangat detail sehingga cukup membaca file ini saja untuk langsung mengerjakan tanpa perlu melihat `docs/ProjectPPK2026.md`.

## Pendahuluan

### Tujuan Dokumen
- Panduan implementasi terstruktur untuk AI Coding Agent.
- Setiap SRS diimplementasikan secara lengkap dan konsisten.
- Implementasi dilakukan berdasarkan urutan SRS yang telah ditentukan.
- Setiap SRS diselesaikan sepenuhnya sebelum lanjut ke SRS berikutnya.
- Tidak menambahkan fitur di luar daftar SRS tanpa konfirmasi user.
- Tidak mengubah arsitektur Laravel, desain database, atau API/route yang sudah ditetapkan.

### Prinsip Implementasi
- `docs/ProjectPPK2026.md` adalah landasan kebutuhan; detail teknis ada di dokumen ini.
- Implementasi mengikuti urutan SRS-01 → SRS-21. Satu SRS selesai penuh sebelum lanjut.
- Semua validasi dilakukan BOTH server-side (Form Request + Service) DAN client-side (Alpine/JS).
- Tiap SRS menulis PHPUnit Feature Test dan menjalankan `php artisan test`.
- Status progres ditandai `[ ]` (belum) / `[x]` (selesai).

### Tech Stack
- Laravel 13, PHP 8.5, Blade + Tailwind CSS, Laravel Breeze, Vite.
- MySQL/MariaDB, database `reservasi_ruangan` (auto-create via SRS-01).
- Session/cache/queue: database driver.
- Enums (Role, FacilityStatus, ReservationStatus, ReportStatus) di `app/Enums`.
- Config bisnis di `config/reservation.php`.
- Code format: `./vendor/bin/pint`.

### Aturan Bisnis GLOBAL
1. **Jam operasional**: 07.00–20.00; slot 30 menit; start/end harus kelipatan 30.
2. **Anti-bentrok**: satu fasilitas tidak boleh punya 2 reservasi approved dengan slot tumpang tindih. Hanya approved yang mengunci slot.
3. **Registrasi mandiri**: role=user + is_verified=false. Petugas TIDAK bisa daftar mandiri. Login ditolak jika is_verified=false.
4. **Fasilitas maintenance/inactive** tidak bisa di-reservasi.
5. **Pembatalan**: user maks 2 jam sebelum start; petugas bisa cancel darurat (approved) dengan alasan.
6. **Logging**: setiap perubahan status dicatat di `reservation_logs`/`report_logs`.
7. **Hak akses**: `admin/*` → role:admin; `staff/*` → role:staff; `/dashboard`+`/reservations`+`/reports` → role:user + verified.

### Status Pelacakan

| SRS | Nama | Status |
|---|---|---|
| SRS-01 | Fondasi Database & Struktur | [x] |
| SRS-02 | Register (Registrasi Mandiri) | [x] |
| SRS-03 | Login (Breeze + Verifikasi Admin) | [x] |
| SRS-04 | Logout | [x] |
| SRS-05 | Akses & Role Middleware | [x] |
| SRS-06 | Daftar Fasilitas (Public) | [x] |
| SRS-07 | Cari & Filter Fasilitas (Public) | [x] |
| SRS-08 | Ketersediaan Fasilitas per Slot (Public) | [x] |
| SRS-09 | Ajukan Reservasi (+ ReservationService) | [x] |
| SRS-10 | Riwayat & Detail Reservasi | [x] |
| SRS-11 | Batalkan Reservasi (User) | [x] |
| SRS-12 | Lapor Kerusakan (Upload Foto) | [x] |
| SRS-13 | Status & Detail Laporan (User) | [x] |
| SRS-14 | Proses Antrian Reservasi (Staff) | [x] |
| SRS-15 | Proses Antrian Laporan (Staff) | [x] |
| SRS-16 | Kelola Fasilitas (Admin CRUD) | [x] |
| SRS-17 | Kelola Pengguna (Admin) | [x] |
| SRS-18 | Rekap & Export (Admin) | [x] |
| SRS-19 | Seeder Data Demo | [x] |
| SRS-20 | Dashboard Per Role | [x] |
| SRS-21 | Pengujian Akhir & Polish | [x] |

### Urutan Implementasi
SRS-01 → SRS-02 → SRS-03 → SRS-04 → SRS-05 → SRS-06 → SRS-07 → SRS-08 → SRS-09 → SRS-10 → SRS-11 → SRS-12 → SRS-13 → SRS-14 → SRS-15 → SRS-16 → SRS-17 → SRS-18 → SRS-19 → SRS-20 → SRS-21

---

# SRS-01: Fondasi Database & Struktur

## Tujuan
Membangun fondasi data: skema database lengkap dalam migration (termasuk auto-create database), semua model Eloquent dengan relasi, konfigurasi bisnis terpusat, skeleton service, dan storage upload.

## Initial State
Laravel 13 + Breeze ter-setup; `.env` ada dengan `DB_DATABASE=reservasi_ruangan`; tabel `users/cache/jobs` sudah dimigrasi; migration facilities/reservations/reports/*_logs masih stub; model User/Facility/Reservation/Report ada sebagai stub; enums lengkap sudah ada; `bootstrap/app.php` punya alias `role`.

## Dependencies
Tidak ada. SRS-01 adalah fondasi.

## Implementation Steps

### A. Konfigurasi Bisnis (`config/reservation.php`)
- [x] Buat file `config/reservation.php`: `open_time=07:00`, `close_time=20:00`, `slot_minutes=30`, `cancel_hours_before=2`, `max_duration_hours=8`, `photo_max_kb=2048`, `photo_mimes=[jpg,jpeg,png]`, `report_categories=[listrik,ac,furniture,plumbing,it,lainnya]`
- [x] Load via `config('reservation.*')` di semua modul.

### B. Migration Lengkap
#### users (tambah kolom)
- [x] Migration baru `add_role_and_verification_to_users_table`:
  - `$table->enum('role',['admin','staff','user'])->after('password')->default('user')`
  - `$table->boolean('is_verified')->after('role')->default(false)`
- [x] Model `User`: `fillable=['name','email','password','role','is_verified']`, `casts=['is_verified'=>boolean]`

#### facilities
- [x] `Schema::create('facilities')`: id, name(string 100), type(string 50), location(string 100), capacity(integer), description(text), status(string 20 default 'active'), timestamps
- [x] Index: `['type']`, `['location']`, `['status']`, `['name']`

#### reservations
- [x] `Schema::create('reservations')`: id, user_id(FK cascade), facility_id(FK cascade), reservation_date(DATE), start_time(TIME), end_time(TIME), purpose(string 255), status(string 20 default 'pending'), reject_reason(nullable), cancel_reason(nullable), processed_by(FK nullable), timestamps
- [x] Index gabungan `['facility_id','reservation_date','start_time']` (anti-bentrok) + index `['status']`, `['reservation_date']`, `['processed_by']`

#### reports
- [x] `Schema::create('reports')`: id, user_id(FK cascade), facility_id(FK cascade), category(string 50), description(text), photo_path(nullable string 255), resolution_note(nullable string 500), status(string 20 default 'new'), processed_by(FK nullable), timestamps
- [x] Index: `['status']`, `['facility_id']`, `['category']`

#### reservation_logs & report_logs
- [x] Sama struktur: id, FK ke tabel utama, actor_id(FK nullable users), action(string 50), old_status(newable), new_status(nullable), note(nullable), timestamps

### C. Auto-Create Database — Custom Command
- [x] Buat `app/Console/Commands/DatabaseCreateCommand.php` (signature `db:create`):
  - Ambil env DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_DATABASE
  - Konek ke MySQL tanpa database via PDO, eksekusi `CREATE DATABASE IF NOT EXISTS reservasi_ruangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`
  - Idempotent: jika DB sudah ada → info, tidak error
- [x] Tambahkan ke `composer.json` script `setup`: `["composer install","cp .env.example .env","php artisan key:generate","php artisan db:create","php artisan migrate --force","npm install --ignore-scripts","npm run build"]`
- [x] Perbarui `README.md` & `docs/DEPLOYMENT.md`: instruksi `composer run setup`.

### D. Models & Relasi
- [x] **User**: relasi `reservations()`, `reports()`
- [x] **Facility**: relasi `reservations()`, `reports()`
- [x] **Reservation**: relasi `user()`, `facility()`, `logs()`
- [x] **Report**: relasi `user()`, `facility()`, `logs()`
- [x] Buat model `ReservationLog` dan `ReportLog` di `app/Models`

### E. Service Skeleton
- [x] `ReservationService::checkConflict($facilityId, $date, $start, $end, $excludeId=null): bool`
- [x] `ReservationService::validateTimeSlot($start, $end): array`
- [x] `ReservationService::createLog($reservation, $action, $old=null, $new=null, $note=null, $actorId=null): void`
- [x] `ReservationService::slotsForDate(): array` → ['07:00','07:30',…,'19:30']
- [x] `ReportService::handlePhotoUpload($file): string`
- [x] `ReportService::markFacilityMaintenance($facilityId)`, `markFacilityActive($facilityId)`

### F. Storage Upload
- [x] Pastikan disk `local` root `storage/app`; buat `storage/app/public/reports`
- [x] `php artisan storage:link`

## Acceptance Criteria
- [x] `php artisan migrate:fresh` membuat semua tabel benar (users dgn role/is_verified, facilities, reservations, reports, reservation_logs, report_logs, cache, jobs, sessions)
- [x] `php artisan db:create` bekerja dan idempotent
- [x] `composer run setup` membuat `.env`, key, DB, migrate otomatis
- [x] Models punya relasi & casts Enum
- [x] `config('reservation.open_time')` = '07:00'
- [x] `php artisan route:list` tetap 66+ route tanpa error

## Testing Checklist
- Positive: migrations jalan, DB ter-create via command
- Negative: DB sudah ada → idempotent; DB_CONNECTION=sqlite → info not applicable
- Error: MySQL mati → pesan jelas

## Completion State
Semua DB & model siap; tim lain bisa `composer run setup`; SRS-02 dapat dimulai.

---

# SRS-02: Register (Registrasi Mandiri Pengguna)

## Tujuan
Mengimplementasikan registrasi mandiri (Breeze) untuk role=user. Akun terdaftar mandiri **wajib diverifikasi admin** sebelum login. Tidak ada pilihan role di form. Petugas tidak bisa daftar mandiri dalam kondisi apa pun.

## Initial State
SRS-01 selesai. Model User punya role/is_verified. Breeze auth route `/register` ada.

## Dependencies
SRS-01 (schema users + model).

## Implementation Steps

### Backend
- [x] Edit `AuthenticatedSessionController@store` atau `RegisteredUserController@store`:
  - Tetapkan `role='user'` & `is_verified=false` saat create
  - **Jangan auto-login** setelah register. Redirect ke `/login` + flash "Pendaftaran berhasil. Menunggu verifikasi admin."
- [x] Buat FormRequest `app/Http/Requests/Auth/RegisterRequest.php`:
  - `name`: required|string|max:255
  - `email`: required|email|max:255|unique:users,email
  - `password`: required|confirmed|min:8
- [x] Model User: cast `is_verified` => boolean; default false.

### Routes
- [x] `GET /register` & `POST /register` public, middleware `guest`.

### View
- [x] Edit `resources/views/auth/register.blade.php` (Breeze + Tailwind):
  - Form: Nama, Email, Password, Confirm Password (tanpa dropdown role)
  - Client validation: required, email format, min 8 password
  - Teks: "Setelah mendaftar, akun Anda menunggu verifikasi admin."
  - Flash success ditampilkan di halaman login.

## Business Rules
- Semua pendaftar baru role=user, is_verified=false. Email unique. Tidak auto-login. Password di-hash.

## Validation Rules
- name wajib string max 255. email wajib format valid unique. password wajib min 8 confirmed.

## Use Case Boundary
Boleh: registrasi & redirect login, flash. TIDAK boleh: login flow (SRS-03).

## Acceptance Criteria
- [x] POST /register valid → user tersimpan role=user, is_verified=false, redirect login
- [x] Email duplikat → error. Password tidak match → error. Field kosong → error.

## Testing Checklist
- [x] Positive: register valid → user tersimpan, redirect login
- [x] Negative: email duplikat, password tidak match, field kosong → error

## Completion State
Register selesai; SRS-03 Login dapat dimulai.

---

# SRS-03: Login (Breeze + Verifikasi Admin)

## Tujuan
Mengimplementasikan login dengan email+password (Breeze). Login berhasil hanya jika `is_verified=true`. Setelah login, redirect sesuai role.

## Initial State
SRS-02 selesai. Breeze login route & view ada.

## Dependencies
SRS-02, SRS-05 (redirect per role).

## Implementation Steps

### Backend
- [x] Edit `AuthenticatedSessionController@store`:
  - Validation: `email` required|email, `password` required
  - Cari user; jika tidak ada → error generic
  - Jika `is_verified=false` → redirect back('login') + withErrors(['email' => 'Akun Anda belum diverifikasi admin.'])
  - Jika verified → attempt($credentials) + regenerate session + redirect Intended sesuai role (helper di SRS-05)
  - Jangan auto-login jika is_verified=false.

### Routes & View
- [x] Route Breeze login public (`guest`). View `auth/login` + link ke register + flash status.

## Business Rules
- Login tanpa verified → ditolak. Setelah login redirect ke dashboard sesuai role (admin→/admin/dashboard, staff→/staff/dashboard, user→/dashboard). Remember me aktif.

## Validation Rules
- Email & password required. Email format.

## Use Case Boundary
Boleh: login, verifikasi gate is_verified, redirect. TIDAK boleh: logout (SRS-04).

## Acceptance Criteria
- [x] User verified → login → redirect sesuai role
- [x] User unverified → ditolak + pesan
- [x] Kredensial salah → error
- [x] Akses `/login` saat sudah login → redirect dashboard

## Testing Checklist
- [x] Positive: login verified → redirect; login admin/staff → redirect sesuai role
- [x] Negative: user unverified → ditolak; kredensial salah → error

## Completion State
Login berfungsi; SRS-04 Logout, kemudian SRS-05 middleware.

---

# SRS-04: Logout

## Tujuan
Mengimplementasikan logout yang menghancurkan session dan mengarahkan user ke `/`.

## Initial State
Breeze default: route `POST /logout` di `routes/auth.php`, controller `AuthenticatedSessionController@destroy`, sudah ada.

## Dependencies
SRS-03.

## Implementation Steps
- [x] Verifikasi `destroy(Request $request)`: `Auth::guard('web')->logout()`, `$request->session()->invalidate()`, `$request->session()->regenerateToken()`, `return redirect('/')`
- [x] Pastikan `POST /logout` dalam group `['middleware' => ['auth']]`
- [x] Button logout di layout: `<form method="POST" action="/logout">` + `@csrf`

## Acceptance Criteria
- [x] POST /logout → session hancur → redirect `/`
- [x] Akses halaman protected setelah logout → redirect `/login`

## Testing Checklist
- [x] Positive: POST /logout → session hancur → redirect /
- [x] Negative: akses halaman protected setelah logout → redirect /login (SRS-05 middleware)

## Completion State
Logout siap; SRS-05.

---

# SRS-05: Akses & Role Middleware

## Tujuan
Menjamin otorisasi berbasis role: pengunjung tanpa login hanya akses halaman public; user/login mendapat akses sesuai peran. Redirect post-login per role. Proteksi verified, middleware `role`, tampilan 403.

## Initial State
`bootstrap/app.php` punya alias `'role' => RoleMiddleware::class`. Routes admin/staff terpisah.

## Dependencies
SRS-03, SRS-04.

## Implementation Steps

### Backend Middleware
- [x] Pastikan `app/Http/Middleware/RoleMiddleware.php`: `handle(Request $request, Closure $next, string ...$roles)`
  - Jika tidak user → redirect('/login')
  - Jika role tidak di dalam `$roles` → abort(403)
  - Membaca `$user->role` (string)
- [x] Helper redirect post-login: `App\Support\AuthRedirect::getDashboardForRole(Role $role)` → return path
- [x] (Optional) `app/Http/Middleware/VerifiedOnly` — cek `is_verified`.

### Routing Final
- [x] `routes/web.php`: public `/`, `/facilities`, `/facilities/{id}/availability`; group `['middleware'=>['auth','verified']]` → `/dashboard`, `profile`, group `middleware('role:user')` → resources `reservations`, `reports`
- [x] `routes/admin.php`: group `['middleware'=>['auth','verified','role:admin']]` → `/admin/dashboard`, `/admin/facilities/*`, `/admin/users/*`, `/admin/reports/*`
- [x] `routes/staff.php`: group `['middleware'=>['auth','verified','role:staff']]` → `/staff/dashboard`, `/staff/reservations/queue*`, `/staff/reports/queue*`
- [x] Halaman 403: `resources/views/errors/403.blade.php`

## Acceptance Criteria
- [x] `GET /admin/dashboard` oleh user → 403
- [x] `GET /staff/dashboard` oleh admin → 403
- [x] Route public accessible tanpa login
- [x] Setelah login, user/admin/staff sampai dashboard masing-masing

## Testing Checklist
- [x] Positive: user/admin/staff dapat mengakses dashboard sesuai role
- [x] Negative: GET /admin/dashboard oleh user → 403; GET /staff/dashboard oleh admin → 403
- [x] Public routes (`/`, `/facilities`) tetap bisa diakses tanpa login

## Completion State
Otorisasi seluruh app kokoh; SRS-06 mulai halaman public.

---

# SRS-06: Daftar Fasilitas (Public)

## Tujuan
Menampilkan daftar fasilitas beserta status (`active`/`maintenance`/`inactive`) ke pengunjung/pengguna tanpa login. Halaman `/` dan `/facilities`. Kartu fasilitas: nama, tipe, lokasi, kapasitas, badge status.

## Dependencies
SRS-01 (facilities), SRS-05 (routing public).

## Implementation Steps

### Backend
- [x] `PublicController::index()` → `view('public.index', compact('facilities'))`
- [x] `PublicController::facilities()` → `view('public.facilities', compact('facilities'))`
- [x] Query: `Facility::whereIn('status',['active','maintenance'])->orderBy('name')->paginate(9)`
- [x] Pass `FacilityStatus::cases()` untuk badge.

### Routes
- [x] `GET /` → name `home`
- [x] `GET /facilities` → name `facilities.index`

### View
- [x] `resources/views/public/index.blade.php`: hero + ringkasan + card grid fasilitas + CTA
- [x] `resources/views/public/facilities.blade.php`: grid kartu + pencarian form (SRS-07) + pagination
- [x] Component `public/facility-card`: name, type, location, capacity, deskripsi singkat; badge status (warna `FacilityStatus::color()`). Link ke halaman ketersediaan (SRS-08).

## Business Rules
- Fasilitas `inactive` tidak ditampilkan publik (hanya active+maintenance). Tanpa detail pemohon.

## Acceptance Criteria
- [x] `/` menampilkan hero + fasilitas
- [x] `/facilities` menampilkan grid dengan badge status
- [x] Dapat diakses tanpa login

## Testing Checklist
- [x] Positive: halaman `/` dan `/facilities` berhasil dirender tanpa login
- [x] Positive: fasilitas hanya menampilkan status active dan maintenance
- [x] Positive: daftar fasilitas diurutkan berdasarkan nama dan dipaginasi 9 data
- [x] Negative: fasilitas inactive tidak ditampilkan publik

## Completion State
Public list siap; SRS-07 menambah filter.

---

# SRS-07: Cari & Filter Fasilitas (Public)

## Tujuan
Memungkinkan pengunjung/pengguna mencari fasilitas berdasarkan **tipe**, **lokasi**, dan **kapasitas minimum** (us.story #2).

## Dependencies
SRS-06.

## Implementation Steps

### Backend
- [x] `PublicController::facilities()` extend query:
  - Terima query params `type`, `location`, `capacity_min`, `search` (nullable)
  - `Facility::when($search, ...)->when($type, ...)->when($location, ...)->when($capacity_min, ...)->whereIn('status',['active','maintenance'])->orderBy('name')`
  - Ambil list unik type & location utk dropdown

### View
- [x] Form filter di atas list: Dropdown Tipe dinamis, Dropdown Lokasi dinamis, Dropdown Kapasitas min, Input Nama Fasilitas, tombol [Cari] & [Reset]. Filter via GET query string. Tandai hasil "Menampilkan X fasilitas".

## Validation Rules
- capacity_min numeric >= 0. type & location max 100.

## Completion State
Public search siap; SRS-08 availability.

---

# SRS-08: Ketersediaan Fasilitas per Slot (Public)

## Tujuan
Menampilkan grid ketersediaan satu fasilitas untuk memilih tanggal: slot 30 menit (07:00–20:00), kolom = status per hari. **Tanpa detail pemohon/tujuan** (us.story #1). Halaman `/facilities/{id}/availability`.

## Dependencies
SRS-01, SRS-07, SRS-05.

## Implementation Steps

### Backend
- [x] `PublicController::facilityAvailability(int $id)`:
  - `$facility = Facility::findOrFail($id)`; maintenance → banner "sedang dalam perbaikan"; inactive → tidak tersedia
  - Ambil `$date` query param (default hari ini)
  - Ambil reservasi **approved** untuk facility & tanggal tsb: `Reservation::where('facility_id',$id)->where('reservation_date',$date)->where('status','approved')->get()`
  - Pass `occupiedTimes` (map jam→bool) + `$slots` (via `ReservationService::slotsForDate()`)

### View
- [x] `views/public/facility-availability.blade.php`:
  - Header: fasilitas info, tanggal [📅], tombol [Lihat]
  - Grid slot 07:00..19:30 → 🟩 kosong / 🟥 terbooking / ⚪ di luar jam / 🟠 fasilitas perbaikan
  - Tidak menampilkan nama pemohon/tujuan
  - Jika belum login → tombol "Ajukan Reservasi" → login

## Business Rules
- Hanya status `approved` yang memblokir slot. `pending` tidak mengunci.
- Maintenance → semua slot tidak bisa di-reservasi.

## Acceptance Criteria
- [x] GET `/facilities/{id}/availability` → grid slot benar
- [x] Approved reservasi tampil sebagai terbooking
- [x] Tanpa detail pemohon/tujuan
- [x] Maintenance → warning

## Completion State
Public availability siap; SRS-09 (ajukan reservasi) menggunakan data ini.

---

# SRS-09: Ajukan Reservasi (Pengguna)

## Tujuan
Pengguna login (role=user) mengajukan reservasi: pilih fasilitas (active), tanggal, start/end time (slot 30 menit), tujuan. Validasi jam operasional & kelipatan 30, cek anti-bentrok dan fasilitas maintenance/closed, simpan status pending, catat log `created`. Semua validasi dilakukan di **server** (ReservationService) dan diperkuat client-side.

## Dependencies
SRS-01 (service skeleton), SRS-05, SRS-08.

## Implementation Steps

### Service — ReservationService (penuh)
- [x] `validateTimeSlot(string $start, string $end): array`:
  - Format `H:i`. Cek `$start < $end`.
  - Dalam batas open–close (>=07:00, <=20:00).
  - Mulai/akhir kelipatan 30 menit (menit % 30 === 0).
- [x] `checkConflict($facilityId, $date, $start, $end, $excludeId=null): bool`:
  - Query: `Reservation::where('facility_id',$facilityId)->where('reservation_date',$date)->where('status','approved')->where(function($q) use ($start,$end) { $q->where('start_time','<',$end)->where('end_time','>',$start); })->when($excludeId, fn($q)=>$q->where('id','!=',$excludeId))->exists()`
- [x] `checkAvailableFacility(Facility $facility): string|null` return error msg kalau status bukan active.
- [x] `createLog(Reservation $reservation, string $action, $old=null, $new=null, $note=null, $actorId=null): void` — simpan `reservation_logs`.

### Controller
- [x] `User\ReservationController@create`: load facilities active + slots → `view('user.reservations.create')`
- [x] `@store(StoreReservationRequest $request)` (Buat `app/Http/Requests/User/StoreReservationRequest.php`):
  - Rules: `facility_id` required|exists:facilities,id; `reservation_date` required|date|after_or_equal:today; `start_time` required|date_format:H:i; `end_time` required|date_format:H:i|after:start_time; `purpose` required|string|max:500
  - Server validation via `withValidator` yang memanggil: `validateTimeSlot`, `checkConflict`, `checkAvailableFacility`
  - Jika valid → `Reservation::create([... 'status'=>ReservationStatus::PENDING->value])`; `createLog($res, 'created', null, 'pending', 'Pengajuan reservasi', auth()->id())`
  - Redirect route('reservations.index') flash success

### Client-side Validation
- [x] View `create.blade.php` (Alpine): dropdown active, date input min today, time inputs (start/end), tujuan textarea. Disable submit bila start>=end atau slot tidak valid. Tampilkan preview validation.

## Business Rules
- User hanya dapat reservasi utk dirinya sendiri. Status awal `pending`. Fasilitas maintenance/inactive tidak bisa dipesan. Bentrok (approved) ditolak. Slot di luar jam/kelipatan 30 ditolak. Date before today ditolak.

## Validation Rules
- Server: facility_id exists+active, date not past, times format & within slot rules, purpose required.
- Client: matching + disable.

## Acceptance Criteria
- [x] Reservasi valid → status pending + log created
- [x] Bentrok approved → error
- [x] Maintenance → error
- [x] Jam/slot salah → error server

## Completion State
User bisa ajukan reservasi; SRS-10 (history/detail) & SRS-11 (cancel).

---

# SRS-10: Riwayat & Detail Reservasi (Pengguna)

## Tujuan
Menampilkan riwayat reservasi user beserta status dan halaman detail lengkap termasuk log perubahan (`reservation_logs`). (us.story #5)

## Dependencies
SRS-09, SRS-01.

## Implementation Steps

### Controller
- [x] `User\ReservationController@index`: `auth()->user()->reservations()->with('facility')->latest()->paginate(10)` → `view('user.reservations.index')`
- [x] `@show(Reservation $reservation)`: abort_unless(owner OR staff/admin, 403); view dengan `logs` (`$reservation->logs()->with('actor')->latest()`) + facility.

### View
- [x] `index.blade.php`: tabel ID, fasilitas, tanggal, jam, status (badge warna `ReservationStatus::label/color`), aksi Lihat / (Batal bila status=pending/approved & dalam batas cancel).
- [x] `show.blade.php`: detail lengkap (fasilitas, tanggal, jam, tujuan, status, alasan) + timeline `logs`.

## Acceptance Criteria
- [x] /reservations menampilkan riwayat
- [x] /reservations/{id} menampilkan detail + log
- [x] User lain tidak bisa lihat (403)

## Completion State
Riwayat siap; SRS-11 cancel.

---

# SRS-11: Batalkan Reservasi (Pengguna)

## Tujuan
Pengguna membatalkan reservasi sendiri (status `pending` atau `approved`) selama masih dalam batas waktu (config `cancel_hours_before` jam sebelum start). Setelah batal, slot kosong kembali. Log `cancelled` oleh user, `cancel_reason` diisi.

## Dependencies
SRS-10, SRS-01 (config, log).

## Implementation Steps

### Controller
- [x] `User\ReservationController@cancel(Reservation $reservation)` (POST):
  - Authority: `abort_unless($reservation->user_id === auth()->id(), 403)`
  - Status valid: `pending` | `approved`
  - Deadline: `now()->diffInMinutes($reservation->reservation_date.' '.$reservation->start_time) >= config('reservation.cancel_hours_before')*60`
  - `cancel_reason` required (min 3 chars)
  - Update status `cancelled`, `cancel_reason`; createLog(reservation, 'cancelled', oldStatus, 'cancelled', reason, actor=id)
  - Redirect back flash success

## Business Rules
- Hanya pemilik. Hanya pending/approved. Batas waktu cancel. `cancel_reason` wajib. Log tercatat.

## Acceptance Criteria
- [x] User batal → status cancelled + reason & log
- [x] Status approved+yang lewat batas ditolak
- [x] Status rejected/cancelled tidak bisa batal

## Completion State
Cancel user siap; lanjut SRS-12 report.

---

# SRS-12: Lapor Kerusakan Fasilitas (Pengguna + Upload Foto)

## Tujuan
Pengguna melaporkan kerusakan/persoalan fasilitas: memilih fasilitas, kategori, deskripsi, dan foto (opsional). Status awal `new`. Foto disimpan ke storage public. (us.story #6)

## Dependencies
SRS-01 (storage, config, Report model), SRS-05.

## Implementation Steps

### Controller
- [x] `User\ReportController@create`: tampilkan semua facility + categories dari config → `view('user.reports.create')`
- [x] `@store(StoreReportRequest $request)`:
  - Rules: `facility_id` required|exists:facilities,id; `category` required|in:config('reservation.report_categories'); `description` required|string|max:2000; `photo` nullable|image|mimes:jpg,jpeg,png|max:2048
  - Upload: jika ada file → `ReportService::handlePhotoUpload($request->file('photo'))` → simpan `storage/app/public/reports/{str_random}.{ext}` → return path `reports/{name}`
  - Simpan `Report::create([... 'status'=>'new'])`; createLog aksi `created`
  - Redirect route('reports.index') flash sukses

### View
- [x] `views/user/reports/create.blade.php`: form (facility select, category select, description textarea, photo input file), preview gambar (JS).
- [x] `views/user/reports/index.blade.php` stub (diisi SRS-13).

## Business Rules
- User lapor utk dirinya. Photo opsional. Status awal `new`. Max 2MB, mimes jpg/jpeg/png. Kategori dari config.

## Acceptance Criteria
- [x] Laporan tersimpan status new + log created
- [x] Foto tersimpan & path valid di storage/app/public/reports

## Completion State
Laporan user siap; SRS-13 status & detail.

---

# SRS-13: Status & Detail Laporan (Pengguna)

## Tujuan
Menampilkan riwayat laporan user beserta status (new/in_progress/resolved/rejected) dan detail lengkap termasuk foto & report_logs. (us.story #7)

## Dependencies
SRS-12, SRS-01.

## Implementation Steps

### Controller
- [x] `index`: laporan user dgn facility, latest, paginate.
- [x] `show(Report $report)`: abort_unless(owner OR staff/admin, 403); view dengan logs + photo URL (`Storage::disk('public')->url($report->photo_path)`).

### View
- [x] `index.blade.php`: tabel (fasilitas, kategori, tanggal, status badge `ReportStatus::label/color`).
- [x] `show.blade.php`: detail (foto jika ada, kategori, deskripsi, resolution_note, status) + timeline logs.

## Completion State
User module selesai; SRS-14 mulai staff.

---

# SRS-14: Proses Antrian Reservasi (Petugas)

## Tujuan
Petugas melihat antrian reservasi pending, menyetujui/menolak/membatalkan (darurat). **Sistem mencegah persetujuan reservasi yang bentrok** dengan reservasi `approved` lain di fasilitas & waktu sama. Pembatalan darurat hanya utk status approved + wajib alasan. Semua aksi dicatat di `reservation_logs`.

## Dependencies
SRS-09 (service conflict), SRS-05, SRS-01 (logs).

## Implementation Steps

### Service
- [x] `ReservationService::approve(Reservation $res, int $actorId)`:
  - Jika status bukan pending → tolak
  - Cek facility active
  - Cek `checkConflict` → jika true → throw error "Terjadi bentrok dengan reservasi lain"
  - Update status approved, processed_by; createLog (approved, actor)
- [x] `reject(Reservation $res, string $reason, int $actorId)` — pending → rejected
- [x] `cancelForced(Reservation $res, string $reason, int $actorId)` — approved → cancelled, createLog

### Controller & Routes (Staff)
- [x] `Staff\ReservationController@queue`: reservasi status pending order by reservation_date,start_time asc, with facility+user → `views/staff/reservations/queue`
- [x] `@approve` (POST `staff.reservations.approve`), `@reject` (POST `staff.reservations.reject`, reject_reason required), `@cancel` (POST, cancel_reason required, hanya approved)

### View
- [x] `queue.blade.php`: kartu per reservasi (user pemohon, fasilitas, tanggal, jam, tujuan). Tombol: [✓ Setujui] (dijam bila bentrok), [✗ Tolak] (modal alasan), [Batalkan] (jika approved; modal alasan). Badge status warna.
- [x] Hitung bentrok per baris via `checkConflict` di controller → flag `isConflict` → disable tombol approve.

## Business Rules
- Approve hanya pending. Approve DITOLAK jika bentrok. Reject wajib alasan. Cancel darurat hanya approved, wajib alasan. Semua log/actor tercatat.

## Acceptance Criteria
- [x] Approve sukses → approved & log
- [x] Bentrok → approve ditolak
- [x] Reject/cancel dengan alasan berfungsi
- [x] Slot approved terkunci

## Completion State
Staff reservasi siap; SRS-15 staff laporan.

---

# SRS-15: Proses Antrian Laporan (Petugas)

## Tujuan
Petugas memproses laporan: mengubah status (new→in_progress→resolved/rejected), mengisi catatan resolusi, menandai fasilitas `maintenance` saat ditangani, mengembalikan fasilitas ke `active` setelah selesai diperbaiki (us.story #11-12). Semua aksi dicatat di `report_logs`.

## Dependencies
SRS-13, SRS-01, SRS-05.

## Implementation Steps

### Service
- [x] `ReportService::updateStatus(Report $report, ReportStatus $status, ?string $note, int $actorId)`:
  - Transisi valid: new→in_progress; in_progress→resolved (wajib note); in_progress→rejected (wajib note); new→resolved|rejected (wajib note)
  - Set status, resolution_note, processed_by; createLog (report_logs) dgn action status_changed/resolved
- [x] `markFacilityMaintenance(int $facilityId)` → `Facility::find($id)->update(['status'=>'maintenance'])`
- [x] `markFacilityActive(int $facilityId)` → `Facility::find($id)->update(['status'=>'active'])`

### Controller & Routes (Staff)
- [x] `Staff\ReportController@queue`: reports status new & in_progress, with facility+user → `views/staff/reports/queue`
- [x] `@show(Report $report)`: detail + foto + logs
- [x] `@updateStatus(Report $report)` (POST): status in_progress/resolved/rejected, resolution_note required jika resolved/rejected
- [x] `@markMaintenance(Report $report)` (POST): tandai facility maintenance
- [x] `@markActive(Report $report)` (POST): fasilitas kembali active

### View
- [x] `queue.blade`: kartu laporan (fasilitas, kategori, tanggal, status badge). Aksi: [Terima/Mulai Proses] → in_progress; [Tandai Perbaikan] → maintenance; [Selesai] → modal resolution_note → resolved + facility active opsional; [Tolak] → modal note → rejected; [Lihat] detail + foto.

## Acceptance Criteria
- [x] Staff ubah status laporan
- [x] Resolved/Rejected menyimpan resolution_note
- [x] Tandai maintenance → facility status maintenance
- [x] Selesai → facility aktif kembali
- [x] Log tercatat

## Completion State
Staff module selesai; SRS-16 admin master fasilitas.

---

# SRS-16: Kelola Fasilitas (Admin CRUD)

## Tujuan
Admin mengelola data master fasilitas: create, read, update, nonaktifkan; ubah status (active/maintenance/inactive). (us.story #16)

## Dependencies
SRS-01, SRS-05, SRS-06.

## Implementation Steps

### Controller & Request
- [x] `Admin\FacilityController@index`: all facilities w/ count reservations/reports, paginate.
- [x] `@create`, `@store(StoreFacilityRequest)`, `@edit`, `@update(UpdateFacilityRequest)`, `@show`, nonaktifkan (update status inactive).
- [x] FormRequest rules: `name` required|string|max:100, `type` required|string|max:50, `location` required|string|max:100, `capacity` required|integer|min:0, `description` nullable|string|max:1000, `status` required|in:active,maintenance,inactive.
- [x] Hapus hard hanya jika tidak ada reservations/reports terkait; default tombol "Nonaktifkan".

### View
- [x] `index`: tabel (nama, tipe, lokasi, kapasitas, status badge, count reservasi, aksi Edit, [Nonaktifkan|Aktifkan], Show).
- [x] `create`/`edit`: form lengkap + select status.

## Acceptance Criteria
- [x] Tambah/edit berhasil
- [x] Status diubah via form
- [x] Nonaktif → tidak tampil public

## Completion State
Master fasilitas siap; SRS-17 kelola user.

---

# SRS-17: Kelola Pengguna (Admin)

## Tujuan
Admin mendaftarkan akun pengguna (mahasiswa/dosen/staf) dan akun petugas **secara langsung** (petugas tidak registrasi mandiri), serta memverifikasi/menolak akun hasil registrasi mandiri (is_verified). (us.story #13-15)

## Dependencies
SRS-05, SRS-02.

## Implementation Steps

### Controller & Request
- [x] `Admin\UserController@index`: users list w/ filter role+status+search, paginate; include verifikasi column.
- [x] `@create` & `@store(StoreUserRequest)`:
  - Rules: name required, email required|unique, password required|min:8, role required|in:user,staff (admin TIDAK dibuat via form)
  - Saat create: `is_verified=true` (admin langsung aktif)
- [x] `@verify` (POST `admin.users.verify`): set is_verified=true
- [x] `@reject` (POST `admin.users.reject`): set is_verified=false (tampil menunggu)

### View
- [x] `index`: tabel (name, email, role badge, is_verified badge ✅/⏳, aksi [Verifikasi]/[Tolak] untuk unverified, [Edit]).
- [x] `create`: form (name, email, password, role select [user|staff]).
- [x] Jangan tampilkan dropdown role admin.

## Acceptance Criteria
- [x] Admin buat user/staff → langsung aktif
- [x] Verify & reject akun mandiri berfungsi

## Completion State
User management siap; SRS-18 export rekap.

---

# SRS-18: Rekap & Export (Admin)

## Tujuan
Admin melihat rekap okupansi fasilitas dan frekuensi kerusakan per fasilitas/lokasi, dengan **export CSV, Excel, PDF**. (us.story #17)

## Dependencies
SRS-14/15 (data), SRS-16 (facilities), SRS-17.

## Implementation Steps

### Dashboard Rekap
- [x] `Admin\ReportController@index`: filter `from`,`to`,`facility_id`, `location`; tampilkan tabel rekap (okupasi per facility, frekuensi kerusakan).

### Export
- [x] **CSV**: method `exportCsv`: StreamedResponse, `text/csv`, nama file `rekap-{date}.csv`, kolom: tipe, nama, lokasi, jumlah reservasi approved, okupansi %, jumlah laporan.
- [x] **Excel**: `.xls` HTML table (BOM UTF-8).
- [x] **PDF**: install `barryvdh/laravel-dompdf` (composer require). Buat view `views/admin/reports/export-pdf.blade.php` → `PDF::loadView(...)->download()`.

### View
- [x] `admin/reports/index.blade.php`: filter form + tabel rekap + tombol [Export CSV] [Export Excel] [Export PDF].

## Acceptance Criteria
- [x] Rekap tampil
- [x] CSV/Excel/PDF unduh berisi data
- [x] Filter bekerja

## Completion State
Admin export siap; SRS-19 seeder.

---

# SRS-19: Seeder Data Demo

## Tujuan
Menyediakan data demo realistis sehingga aplikasi dan presentasi memiliki data lengkap semua role.

## Dependencies
SRS-01..18.

## Implementation Steps
- [x] `UserSeeder`: 1 admin (admin@kampus.ac.id, role admin, is_verified=true), 2 staff, 8 user (3 di antaranya is_verified=false).
- [x] `FacilitySeeder`: 20 fasilitas bervariasi, mencakup status active dan maintenance.
- [x] `ReservationSeeder`: 22 reservasi (pending, approved, rejected, cancelled; tanpa approved bentrok), dengan reservation logs.
- [x] `ReportSeeder`: 10 reports dengan kategori dan status bervariasi, serta report logs.
- [x] `DatabaseSeeder` panggil: UserSeeder → FacilitySeeder → ReservationSeeder → ReportSeeder.
- [x] Catat credentials demo di README.

## Acceptance Criteria
- [x] `php artisan migrate:fresh --seed` menghasilkan data
- [x] Semua role bisa login (akun verified)

## Completion State
Data demo siap; SRS-20 dashboard.

---

# SRS-20: Dashboard Per Role

## Tujuan
Menyediakan halaman dashboard informatif sesuai role (user/staff/admin) — ringkasan & shortcut.

## Dependencies
SRS-06..18, SRS-05.

## Implementation Steps
- [x] **User Dashboard**: stat reservations per status milik user + laporan per status; list 5 terbaru + shortcuts. `resources/views/dashboard.blade.php`.
- [x] **Staff Dashboard**: count reservasi pending + laporan new/in_progress; preview queue + link langsung ke queue. `resources/views/staff/dashboard.blade.php`.
- [x] **Admin Dashboard**: totals facilities, users, reservations today, reports (per status), top facilities; quick links ke admin pages. `resources/views/admin/dashboard.blade.php`. `views/admin/dashboard.blade.php`.

## Completion State
Dashboard lengkap; SRS-21 polish.

---

# SRS-21: Pengujian Akhir & Polish

## Tujuan
Finalisasi: menjalankan seluruh test, memperbaiki bug, konsistensi UI/Responsive, dokumen & seeded credentials, memastikan seluruh acceptance criteria tiap SRS terpenuhi.

## Dependencies
Semua SRS.

## Implementation Steps
- [x] `php artisan test` — perbaiki semua yang gagal.
- [x] `./vendor/bin/pint` — fix code style.
- [x] `npm run build` — pastikan Vite manifest.
- [x] End-to-end manual smoke test (semua alur dari SRS-02 s/d 18).
- [x] Cek responsive di 3 ukuran layar untuk halaman kunci.
- [x] Cek pesan error/empty state semua halaman.
- [x] Cek CSV/Excel/PDF export benar format.
- [x] Update README: credentials (account demo), langkah setup `composer run setup`, troubleshooting MySQL.
- [x] Update SETUP_COMPLETE.md / COLLABORATION status.

## Acceptance Criteria
- [x] `php artisan test` hijau
- [x] Pint clean
- [x] Semua alur utama berfungsi di manual smoke
- [x] Dokumen berisi login demo & steps

## Completion State
Project siap presentasi/demo. Semua SRS-01..21 selesai. Seluruh dokumentasi lengkap.

---

# Lampiran: Ringkasan Route yang Dipakai

| Method | URI | Name | Handler | Middleware |
|---|---|---|---|---|
| GET | / | home | PublicController@index | public |
| GET | /facilities | facilities.index | PublicController@facilities | public |
| GET | /facilities/{facility}/availability | facilities.availability | PublicController@facilityAvailability | public |
| GET | /dashboard | dashboard | User\DashboardController@index | auth,verified,role:user |
| GET/POST | /reservations/* | reservations.* | User\ReservationController | auth,verified,role:user |
| POST | /reservations/{reservation}/cancel | reservations.cancel | User\ReservationController@cancel | role:user |
| GET/POST | /reports/* | reports.* | User\ReportController | role:user |
| GET | /staff/dashboard | staff.dashboard | Staff\DashboardController | auth,verified,role:staff |
| GET/POST | /staff/reservations/queue* | staff.reservations.* | Staff\ReservationController | role:staff |
| GET/POST | /staff/reports/queue* | staff.reports.* | Staff\ReportController | role:staff |
| GET/POST | /admin/* | admin.* | Admin\*Controller | role:admin |
| GET/POST | /login, /register, /logout | (Breeze) | Auth\* | public/guest/auth |

# Lampiran: Konvensi Enum Value & Warna (badge di seluruh view)

| Enum | Value | Label | Badge Warna |
|---|---|---|---|
| Role | admin/staff/user | Administrator/Petugas/Pengguna | indigo/cyan/gray |
| FacilityStatus | active/maintenance/inactive | Aktif/Dalam Perbaikan/Tidak Aktif | green/yellow/gray |
| ReservationStatus | pending/approved/rejected/cancelled | Menunggu/Disetujui/Ditolak/Dibatalkan | yellow/green/red/gray |
| ReportStatus | new/in_progress/resolved/rejected | Baru/Sedang Diproses/Selesai/Ditolak | blue/yellow/green/red |