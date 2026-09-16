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
| SRS-01 | Fondasi Database & Struktur | [ ] |
| SRS-02 | Register (Registrasi Mandiri) | [ ] |
| SRS-03 | Login (Breeze) | [ ] |
| SRS-04 | Logout | [ ] |
| SRS-05 | Akses & Role Middleware | [ ] |
| SRS-06 | Daftar Fasilitas (Public) | [ ] |
| SRS-07 | Cari & Filter Fasilitas (Public) | [ ] |
| SRS-08 | Ketersediaan Fasilitas per Slot (Public) | [ ] |
| SRS-09 | Ajukan Reservasi (+ ReservationService) | [ ] |
| SRS-10 | Riwayat & Detail Reservasi | [ ] |
| SRS-11 | Batalkan Reservasi (User) | [ ] |
| SRS-12 | Lapor Kerusakan (Upload Foto) | [ ] |
| SRS-13 | Status & Detail Laporan (User) | [ ] |
| SRS-14 | Proses Antrian Reservasi (Staff) | [ ] |
| SRS-15 | Proses Antrian Laporan (Staff) | [ ] |
| SRS-16 | Kelola Fasilitas (Admin CRUD) | [ ] |
| SRS-17 | Kelola Pengguna (Admin) | [ ] |
| SRS-18 | Rekap & Export (Admin) | [ ] |
| SRS-19 | Seeder Data Demo | [ ] |
| SRS-20 | Dashboard Per Role | [ ] |
| SRS-21 | Pengujian Akhir & Polish | [ ] |

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
- [ ] Buat file `config/reservation.php`: `open_time=07:00`, `close_time=20:00`, `slot_minutes=30`, `cancel_hours_before=2`, `max_duration_hours=8`, `photo_max_kb=2048`, `photo_mimes=[jpg,jpeg,png]`, `report_categories=[listrik,ac,furniture,plumbing,it,lainnya]`
- [ ] Load via `config('reservation.*')` di semua modul.

### B. Migration Lengkap
#### users (tambah kolom)
- [ ] Migration baru `add_role_and_verification_to_users_table`:
  - `$table->enum('role',['admin','staff','user'])->after('password')->default('user')`
  - `$table->boolean('is_verified')->after('role')->default(false)`
- [ ] Model `User`: `fillable=['name','email','password','role','is_verified']`, `casts=['is_verified'=>boolean]`

#### facilities
- [ ] `Schema::create('facilities')`: id, name(string 100), type(string 50), location(string 100), capacity(integer), description(text), status(string 20 default 'active'), timestamps
- [ ] Index: `['type']`, `['location']`, `['status']`, `['name']`

#### reservations
- [ ] `Schema::create('reservations')`: id, user_id(FK cascade), facility_id(FK cascade), reservation_date(DATE), start_time(TIME), end_time(TIME), purpose(string 255), status(string 20 default 'pending'), reject_reason(nullable), cancel_reason(nullable), processed_by(FK nullable), timestamps
- [ ] Index gabungan `['facility_id','reservation_date','start_time']` (anti-bentrok) + index `['status']`, `['reservation_date']`, `['processed_by']`

#### reports
- [ ] `Schema::create('reports')`: id, user_id(FK cascade), facility_id(FK cascade), category(string 50), description(text), photo_path(nullable string 255), resolution_note(nullable string 500), status(string 20 default 'new'), processed_by(FK nullable), timestamps
- [ ] Index: `['status']`, `['facility_id']`, `['category']`

#### reservation_logs & report_logs
- [ ] Sama struktur: id, FK ke tabel utama, actor_id(FK nullable users), action(string 50), old_status(newable), new_status(nullable), note(nullable), timestamps

### C. Auto-Create Database — Custom Command
- [ ] Buat `app/Console/Commands/DatabaseCreateCommand.php` (signature `db:create`):
  - Ambil env DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_DATABASE
  - Konek ke MySQL tanpa database via PDO, eksekusi `CREATE DATABASE IF NOT EXISTS reservasi_ruangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`
  - Idempotent: jika DB sudah ada → info, tidak error
- [ ] Tambahkan ke `composer.json` script `setup`: `["composer install","cp .env.example .env","php artisan key:generate","php artisan db:create","php artisan migrate --force","npm install --ignore-scripts","npm run build"]`
- [ ] Perbarui `README.md` & `docs/DEPLOYMENT.md`: instruksi `composer run setup`.

### D. Models & Relasi
- [ ] **User**: relasi `reservations()`, `reports()`
- [ ] **Facility**: relasi `reservations()`, `reports()`
- [ ] **Reservation**: relasi `user()`, `facility()`, `logs()`
- [ ] **Report**: relasi `user()`, `facility()`, `logs()`
- [ ] Buat model `ReservationLog` dan `ReportLog` di `app/Models`

### E. Service Skeleton
- [ ] `ReservationService::checkConflict($facilityId, $date, $start, $end, $excludeId=null): bool`
- [ ] `ReservationService::validateTimeSlot($start, $end): array`
- [ ] `ReservationService::createLog($reservation, $action, $old=null, $new=null, $note=null, $actorId=null): void`
- [ ] `ReservationService::slotsForDate(): array` → ['07:00','07:30',…,'19:30']
- [ ] `ReportService::handlePhotoUpload($file): string`
- [ ] `ReportService::markFacilityMaintenance($facilityId)`, `markFacilityActive($facilityId)`

### F. Storage Upload
- [ ] Pastikan disk `local` root `storage/app`; buat `storage/app/public/reports`
- [ ] `php artisan storage:link`

## Acceptance Criteria
- [ ] `php artisan migrate:fresh` membuat semua tabel benar (users dgn role/is_verified, facilities, reservations, reports, reservation_logs, report_logs, cache, jobs, sessions)
- [ ] `php artisan db:create` bekerja dan idempotent
- [ ] `composer run setup` membuat `.env`, key, DB, migrate otomatis
- [ ] Models punya relasi & casts Enum
- [ ] `config('reservation.open_time')` = '07:00'
- [ ] `php artisan route:list` tetap 66+ route tanpa error

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
- [ ] Edit `AuthenticatedSessionController@store` atau `RegisteredUserController@store`:
  - Tetapkan `role='user'` & `is_verified=false` saat create
  - **Jangan auto-login** setelah register. Redirect ke `/login` + flash "Pendaftaran berhasil. Menunggu verifikasi admin."
- [ ] Buat FormRequest `app/Http/Requests/Auth/RegisterRequest.php`:
  - `name`: required|string|max:255
  - `email`: required|email|max:255|unique:users,email
  - `password`: required|confirmed|min:8
- [ ] Model User: cast `is_verified` => boolean; default false.

### Routes
- [ ] `GET /register` & `POST /register` public, middleware `guest`.

### View
- [ ] Edit `resources/views/auth/register.blade.php` (Breeze + Tailwind):
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
- [ ] POST /register valid → user tersimpan role=user, is_verified=false, redirect login
- [ ] Email duplikat → error. Password tidak match → error. Field kosong → error.

## Testing Checklist
- Positive: register valid → user tersimpan, redirect login
- Negative: email duplikat, password tidak match, field kosong → error

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
- [ ] Edit `AuthenticatedSessionController@store`:
  - Validation: `email` required|email, `password` required
  - Cari user; jika tidak ada → error generic
  - Jika `is_verified=false` → redirect back('login') + error "Akun Anda belum diverifikasi admin."
  - Jika verified → attempt($credentials) + regenerate session + redirect Intended sesuai role (helper di SRS-05)
  - Jangan auto-login jika is_verified=false.

### Routes & View
- [ ] Route Breeze login public (`guest`). View `auth/login` + link ke register + flash status.

## Business Rules
- Login tanpa verified → ditolak. Setelah login redirect ke dashboard sesuai role (admin→/admin/dashboard, staff→/staff/dashboard, user→/dashboard). Remember me aktif.

## Validation Rules
- Email & password required. Email format.

## Use Case Boundary
Boleh: login, verifikasi gate is_verified, redirect. TIDAK boleh: logout (SRS-04).

## Acceptance Criteria
- [ ] User verified → login → redirect sesuai role
- [ ] User unverified → ditolak + pesan
- [ ] Kredensial salah → error
- [ ] Akses `/login` saat sudah login → redirect dashboard

## Testing Checklist
- Positive: login verified → redirect; login admin/staff → redirect sesuai role
- Negative: user unverified → ditolak; kredensial salah → error

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
- [ ] Verifikasi `destroy(Request $request)`: `Auth::guard('web')->logout()`, `$request->session()->invalidate()`, `$request->session()->regenerateToken()`, `return redirect('/')`
- [ ] Pastikan `POST /logout` dalam group `['middleware' => ['auth']]`
- [ ] Button logout di layout: `<form method="POST" action="/logout">` + `@csrf`

## Acceptance Criteria
- [ ] POST /logout → session hancur → redirect `/`
- [ ] Akses halaman protected setelah logout → redirect `/login`

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
- [ ] Pastikan `app/Http/Middleware/RoleMiddleware.php`: `handle(Request $request, Closure $next, string ...$roles)`
  - Jika tidak user → redirect('/login')
  - Jika role tidak di dalam `$roles` → abort(403)
  - Membaca `$user->role` (string)
- [ ] Helper redirect post-login: `App\Support\AuthRedirect::getDashboardForRole(Role $role)` → return path
- [ ] (Optional) `app/Http/Middleware/VerifiedOnly` — cek `is_verified`.

### Routing Final
- [ ] `routes/web.php`: public `/`, `/facilities`, `/facilities/{id}/availability`; group `['middleware'=>['auth','verified']]` → `/dashboard`, `profile`, group `middleware('role:user')` → resources `reservations`, `reports`
- [ ] `routes/admin.php`: group `['middleware'=>['auth','verified','role:admin']]` → `/admin/dashboard`, `/admin/facilities/*`, `/admin/users/*`, `/admin/reports/*`
- [ ] `routes/staff.php`: group `['middleware'=>['auth','verified','role:staff']]` → `/staff/dashboard`, `/staff/reservations/queue*`, `/staff/reports/queue*`
- [ ] Halaman 403: `resources/views/errors/403.blade.php`

## Acceptance Criteria
- [ ] `GET /admin/dashboard` oleh user → 403
- [ ] `GET /staff/dashboard` oleh admin → 403
- [ ] Route public accessible tanpa login
- [ ] Setelah login, user/admin/staff sampai dashboard masing-masing

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
- [ ] `PublicController::index()` → `view('public.index', compact('facilities'))`
- [ ] `PublicController::facilities()` → `view('public.facilities', compact('facilities'))`
- [ ] Query: `Facility::whereIn('status',['active','maintenance'])->orderBy('name')->paginate(9)`
- [ ] Pass `FacilityStatus::cases()` untuk badge.

### Routes
- [ ] `GET /` → name `home`
- [ ] `GET /facilities` → name `facilities.index`

### View
- [ ] `resources/views/public/index.blade.php`: hero + ringkasan + card grid fasilitas + CTA
- [ ] `resources/views/public/facilities.blade.php`: grid kartu + pencarian form (SRS-07) + pagination
- [ ] Component `public/facility-card`: name, type, location, capacity, deskripsi singkat; badge status (warna `FacilityStatus::color()`). Link ke halaman ketersediaan (SRS-08).

## Business Rules
- Fasilitas `inactive` tidak ditampilkan publik (hanya active+maintenance). Tanpa detail pemohon.

## Acceptance Criteria
- [ ] `/` menampilkan hero + fasilitas
- [ ] `/facilities` menampilkan grid dengan badge status
- [ ] Dapat diakses tanpa login

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
- [ ] `PublicController::facilities()` extend query:
  - Terima query params `type`, `location`, `capacity_min` (nullable)
  - `Facility::when($type, fn($q)=>$q->where('type',$type))
    ->when($location, fn($q)=>$q->where('location','like','%'.$location.'%'))
    ->when($capacity_min, fn($q)=>$q->where('capacity','>=',$capacity_min))
    ->whereIn('status',['active','maintenance'])
    ->orderBy('name')->paginate(9)->withQueryString()`
  - Ambil list unik type & location utk dropdown

### View
- [ ] Fish form di atas list: Dropdown Tipe, Text Lokasi, Number Kapasitas min, tombol [Cari] & [Reset]. Filter via GET query string. Tandai hasil "Menampilkan X fasilitas".

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
- [ ] `PublicController::facilityAvailability(int $id)`:
  - `$facility = Facility::findOrFail($id)`; maintenance → banner "sedang dalam perbaikan"; inactive → tidak tersedia
  - Ambil `$date` query param (default hari ini)
  - Ambil reservasi **approved** untuk facility & tanggal tsb: `Reservation::where('facility_id',$id)->where('reservation_date',$date)->where('status','approved')->get()`
  - Pass `occupiedTimes` (map jam→bool) + `$slots` (via `ReservationService::slotsForDate()`)

### View
- [ ] `views/public/facility-availability.blade.php`:
  - Header: fasilitas info, tanggal [📅], tombol [Lihat]
  - Grid slot 07:00..19:30 → 🟩 kosong / 🟥 terbooking / ⚪ di luar jam / 🟠 fasilitas perbaikan
  - Tidak menampilkan nama pemohon/tujuan
  - Jika belum login → tombol "Ajukan Reservasi" → login

## Business Rules
- Hanya status `approved` yang memblokir slot. `pending` tidak mengunci.
- Maintenance → semua slot tidak bisa di-reservasi.

## Acceptance Criteria
- [ ] GET `/facilities/{id}/availability` → grid slot benar
- [ ] Approved reservasi tampil sebagai terbooking
- [ ] Tanpa detail pemohon/tujuan
- [ ] Maintenance → warning

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
- [ ] `validateTimeSlot(string $start, string $end): array`:
  - Format `H:i`. Cek `$start < $end`.
  - Dalam batas open–close (>=07:00, <=20:00).
  - Mulai/akhir kelipatan 30 menit (menit % 30 === 0).
- [ ] `checkConflict($facilityId, $date, $start, $end, $excludeId=null): bool`:
  - Query: `Reservation::where('facility_id',$facilityId)->where('reservation_date',$date)->where('status','approved')->where(function($q) use ($start,$end) { $q->where('start_time','<',$end)->where('end_time','>',$start); })->when($excludeId, fn($q)=>$q->where('id','!=',$excludeId))->exists()`
- [ ] `checkAvailableFacility(Facility $facility): string|null` return error msg kalau status bukan active.
- [ ] `createLog(Reservation $reservation, string $action, $old=null, $new=null, $note=null, $actorId=null): void` — simpan `reservation_logs`.

### Controller
- [ ] `User\ReservationController@create`: load facilities active + slots → `view('user.reservations.create')`
- [ ] `@store(StoreReservationRequest $request)` (Buat `app/Http/Requests/User/StoreReservationRequest.php`):
  - Rules: `facility_id` required|exists:facilities,id; `reservation_date` required|date|after_or_equal:today; `start_time` required|date_format:H:i; `end_time` required|date_format:H:i|after:start_time; `purpose` required|string|max:500
  - Server validation via `withValidator` yang memanggil: `validateTimeSlot`, `checkConflict`, `checkAvailableFacility`
  - Jika valid → `Reservation::create([... 'status'=>ReservationStatus::PENDING->value])`; `createLog($res, 'created', null, 'pending', 'Pengajuan reservasi', auth()->id())`
  - Redirect route('reservations.index') flash success

### Client-side Validation
- [ ] View `create.blade.php` (Alpine): dropdown active, date input min today, time inputs (start/end), tujuan textarea. Disable submit bila start>=end atau slot tidak valid. Tampilkan preview validation.

## Business Rules
- User hanya dapat reservasi utk dirinya sendiri. Status awal `pending`. Fasilitas maintenance/inactive tidak bisa dipesan. Bentrok (approved) ditolak. Slot di luar jam/kelipatan 30 ditolak. Date before today ditolak.

## Validation Rules
- Server: facility_id exists+active, date not past, times format & within slot rules, purpose required.
- Client: matching + disable.

## Acceptance Criteria
- [ ] Reservasi valid → status pending + log created
- [ ] Bentrok approved → error
- [ ] Maintenance → error
- [ ] Jam/slot salah → error server

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
- [ ] `User\ReservationController@index`: `auth()->user()->reservations()->with('facility')->latest()->paginate(10)` → `view('user.reservations.index')`
- [ ] `@show(Reservation $reservation)`: abort_unless(owner OR staff/admin, 403); view dengan `logs` (`$reservation->logs()->with('actor')->latest()`) + facility.

### View
- [ ] `index.blade.php`: tabel ID, fasilitas, tanggal, jam, status (badge warna `ReservationStatus::label/color`), aksi Lihat / (Batal bila status=pending/approved & dalam batas cancel).
- [ ] `show.blade.php`: detail lengkap (fasilitas, tanggal, jam, tujuan, status, alasan) + timeline `logs`.

## Acceptance Criteria
- [ ] /reservations menampilkan riwayat
- [ ] /reservations/{id} menampilkan detail + log
- [ ] User lain tidak bisa lihat (403)

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
- [ ] `User\ReservationController@cancel(Reservation $reservation)` (POST):
  - Authority: `abort_unless($reservation->user_id === auth()->id(), 403)`
  - Status valid: `pending` | `approved`
  - Deadline: `now()->diffInMinutes($reservation->reservation_date.' '.$reservation->start_time) >= config('reservation.cancel_hours_before')*60`
  - `cancel_reason` required (min 3 chars)
  - Update status `cancelled`, `cancel_reason`; createLog(reservation, 'cancelled', oldStatus, 'cancelled', reason, actor=id)
  - Redirect back flash success

## Business Rules
- Hanya pemilik. Hanya pending/approved. Batas waktu cancel. `cancel_reason` wajib. Log tercatat.

## Acceptance Criteria
- [ ] User batal → status cancelled + reason & log
- [ ] Status approved+yang lewat batas ditolak
- [ ] Status rejected/cancelled tidak bisa batal

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
- [ ] `User\ReportController@create`: tampilkan semua facility + categories dari config → `view('user.reports.create')`
- [ ] `@store(StoreReportRequest $request)`:
  - Rules: `facility_id` required|exists:facilities,id; `category` required|in:config('reservation.report_categories'); `description` required|string|max:2000; `photo` nullable|image|mimes:jpg,jpeg,png|max:2048
  - Upload: jika ada file → `ReportService::handlePhotoUpload($request->file('photo'))` → simpan `storage/app/public/reports/{str_random}.{ext}` → return path `reports/{name}`
  - Simpan `Report::create([... 'status'=>'new'])`; createLog aksi `created`
  - Redirect route('reports.index') flash sukses

### View
- [ ] `views/user/reports/create.blade.php`: form (facility select, category select, description textarea, photo input file), preview gambar (JS).
- [ ] `views/user/reports/index.blade.php` stub (diisi SRS-13).

## Business Rules
- User lapor utk dirinya. Photo opsional. Status awal `new`. Max 2MB, mimes jpg/jpeg/png. Kategori dari config.

## Acceptance Criteria
- [ ] Laporan tersimpan status new + log created
- [ ] Foto tersimpan & path valid di storage/app/public/reports

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
- [ ] `index`: laporan user dgn facility, latest, paginate.
- [ ] `show(Report $report)`: abort_unless(owner OR staff/admin, 403); view dengan logs + photo URL (`Storage::disk('public')->url($report->photo_path)`).

### View
- [ ] `index.blade.php`: tabel (fasilitas, kategori, tanggal, status badge `ReportStatus::label/color`).
- [ ] `show.blade.php`: detail (foto jika ada, kategori, deskripsi, resolution_note, status) + timeline logs.

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
- [ ] `ReservationService::approve(Reservation $res, int $actorId)`:
  - Jika status bukan pending → tolak
  - Cek facility active
  - Cek `checkConflict` → jika true → throw error "Terjadi bentrok dengan reservasi lain"
  - Update status approved, processed_by; createLog (approved, actor)
- [ ] `reject(Reservation $res, string $reason, int $actorId)` — pending → rejected
- [ ] `cancelForced(Reservation $res, string $reason, int $actorId)` — approved → cancelled, createLog

### Controller & Routes (Staff)
- [ ] `Staff\ReservationController@queue`: reservasi status pending order by reservation_date,start_time asc, with facility+user → `views/staff/reservations/queue`
- [ ] `@approve` (POST `staff.reservations.approve`), `@reject` (POST `staff.reservations.reject`, reject_reason required), `@cancel` (POST, cancel_reason required, hanya approved)

### View
- [ ] `queue.blade.php`: kartu per reservasi (user pemohon, fasilitas, tanggal, jam, tujuan). Tombol: [✓ Setujui] (dijam bila bentrok), [✗ Tolak] (modal alasan), [Batalkan] (jika approved; modal alasan). Badge status warna.
- [ ] Hitung bentrok per baris via `checkConflict` di controller → flag `isConflict` → disable tombol approve.

## Business Rules
- Approve hanya pending. Approve DITOLAK jika bentrok. Reject wajib alasan. Cancel darurat hanya approved, wajib alasan. Semua log/actor tercatat.

## Acceptance Criteria
- [ ] Approve sukses → approved & log
- [ ] Bentrok → approve ditolak
- [ ] Reject/cancel dengan alasan berfungsi
- [ ] Slot approved terkunci

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
- [ ] `ReportService::updateStatus(Report $report, ReportStatus $status, ?string $note, int $actorId)`:
  - Transisi valid: new→in_progress; in_progress→resolved (wajib note); in_progress→rejected (wajib note); new→resolved|rejected (wajib note)
  - Set status, resolution_note, processed_by; createLog (report_logs) dgn action status_changed/resolved
- [ ] `markFacilityMaintenance(int $facilityId)` → `Facility::find($id)->update(['status'=>'maintenance'])`
- [ ] `markFacilityActive(int $facilityId)` → `Facility::find($id)->update(['status'=>'active'])`

### Controller & Routes (Staff)
- [ ] `Staff\ReportController@queue`: reports status new & in_progress, with facility+user → `views/staff/reports/queue`
- [ ] `@show(Report $report)`: detail + foto + logs
- [ ] `@updateStatus(Report $report)` (POST): status in_progress/resolved/rejected, resolution_note required jika resolved/rejected
- [ ] `@markMaintenance(Report $report)` (POST): tandai facility maintenance
- [ ] `@markActive(Report $report)` (POST): fasilitas kembali active

### View
- [ ] `queue.blade`: kartu laporan (fasilitas, kategori, tanggal, status badge). Aksi: [Terima/Mulai Proses] → in_progress; [Tandai Perbaikan] → maintenance; [Selesai] → modal resolution_note → resolved + facility active opsional; [Tolak] → modal note → rejected; [Lihat] detail + foto.

## Acceptance Criteria
- [ ] Staff ubah status laporan
- [ ] Resolved/Rejected menyimpan resolution_note
- [ ] Tandai maintenance → facility status maintenance
- [ ] Selesai → facility aktif kembali
- [ ] Log tercatat

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
- [ ] `Admin\FacilityController@index`: all facilities w/ count reservations/reports, paginate.
- [ ] `@create`, `@store(StoreFacilityRequest)`, `@edit`, `@update(UpdateFacilityRequest)`, `@show`, nonaktifkan (update status inactive).
- [ ] FormRequest rules: `name` required|string|max:100, `type` required|string|max:50, `location` required|string|max:100, `capacity` required|integer|min:0, `description` nullable|string|max:1000, `status` required|in:active,maintenance,inactive.
- [ ] Hapus hard hanya jika tidak ada reservations/reports terkait; default tombol "Nonaktifkan".

### View
- [ ] `index`: tabel (nama, tipe, lokasi, kapasitas, status badge, count reservasi, aksi Edit, [Nonaktifkan|Aktifkan], Show).
- [ ] `create`/`edit`: form lengkap + select status.

## Acceptance Criteria
- [ ] Tambah/edit berhasil
- [ ] Status diubah via form
- [ ] Nonaktif → tidak tampil public

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
- [ ] `Admin\UserController@index`: users list w/ filter role+status+search, paginate; include verifikasi column.
- [ ] `@create` & `@store(StoreUserRequest)`:
  - Rules: name required, email required|unique, password required|min:8, role required|in:user,staff (admin TIDAK dibuat via form)
  - Saat create: `is_verified=true` (admin langsung aktif)
- [ ] `@verify` (POST `admin.users.verify`): set is_verified=true
- [ ] `@reject` (POST `admin.users.reject`): set is_verified=false (tampil menunggu)

### View
- [ ] `index`: tabel (name, email, role badge, is_verified badge ✅/⏳, aksi [Verifikasi]/[Tolak] untuk unverified, [Edit]).
- [ ] `create`: form (name, email, password, role select [user|staff]).
- [ ] Jangan tampilkan dropdown role admin.

## Acceptance Criteria
- [ ] Admin buat user/staff → langsung aktif
- [ ] Verify & reject akun mandiri berfungsi

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
- [ ] `Admin\ReportController@index`: filter `from`,`to`,`facility_id`, `location`; tampilkan tabel rekap (okupasi per facility, frekuensi kerusakan).

### Export
- [ ] **CSV**: method `exportCsv`: StreamedResponse, `text/csv`, nama file `rekap-{date}.csv`, kolom: tipe, nama, lokasi, jumlah reservasi approved, okupansi %, jumlah laporan.
- [ ] **Excel**: `.xls` HTML table (BOM UTF-8).
- [ ] **PDF**: install `barryvdh/laravel-dompdf` (composer require). Buat view `views/admin/reports/export-pdf.blade.php` → `PDF::loadView(...)->download()`.

### View
- [ ] `admin/reports/index.blade.php`: filter form + tabel rekap + tombol [Export CSV] [Export Excel] [Export PDF].

## Acceptance Criteria
- [ ] Rekap tampil
- [ ] CSV/Excel/PDF unduh berisi data
- [ ] Filter bekerja

## Completion State
Admin export siap; SRS-19 seeder.

---

# SRS-19: Seeder Data Demo

## Tujuan
Menyediakan data demo realistis sehingga aplikasi dan presentasi memiliki data lengkap semua role.

## Dependencies
SRS-01..18.

## Implementation Steps
- [ ] `UserSeeder`: 1 admin (admin@kampus.ac.id, role admin, is_verified=true), 2 staff, 8 user (3 di antaranya is_verified=false).
- [ ] `FacilitySeeder`: 10 fasilitas bervariasi (Ruang 101, Lab Komputer RPL, dll).
- [ ] `ReservationSeeder`: ±20 reservasi (pending, approved, rejected, cancelled; tanpa approved bentrok).
- [ ] `ReportSeeder`: ±10 reports kategori bervariasi.
- [ ] `DatabaseSeeder` panggil: UserSeeder → FacilitySeeder → ReservationSeeder → ReportSeeder.
- [ ] Catat credentials demo di README.

## Acceptance Criteria
- [ ] `php artisan migrate:fresh --seed` menghasilkan data
- [ ] Semua role bisa login (akun verified)

## Completion State
Data demo siap; SRS-20 dashboard.

---

# SRS-20: Dashboard Per Role

## Tujuan
Menyediakan halaman dashboard informatif sesuai role (user/staff/admin) — ringkasan & shortcut.

## Dependencies
SRS-06..18, SRS-05.

## Implementation Steps
- [ ] **User Dashboard**: stat reservations per status milik user + laporan per status; list 5 terbaru + shortcuts. `views/user/dashboard.blade.php`.
- [ ] **Staff Dashboard**: count reservasi pending + laporan new/in_progress; link langsung ke queue. `views/staff/dashboard.blade.php`.
- [ ] **Admin Dashboard**: totals facilities, users, reservations today, reports (per status), top facilities; quick links ke admin pages. `views/admin/dashboard.blade.php`.

## Completion State
Dashboard lengkap; SRS-21 polish.

---

# SRS-21: Pengujian Akhir & Polish

## Tujuan
Finalisasi: menjalankan seluruh test, memperbaiki bug, konsistensi UI/Responsive, dokumen & seeded credentials, memastikan seluruh acceptance criteria tiap SRS terpenuhi.

## Dependencies
Semua SRS.

## Implementation Steps
- [ ] `php artisan test` — perbaiki semua yang gagal.
- [ ] `./vendor/bin/pint` — fix code style.
- [ ] `npm run build` — pastikan Vite manifest.
- [ ] End-to-end manual smoke test (semua alur dari SRS-02 s/d 18).
- [ ] Cek responsive di 3 ukuran layar untuk halaman kunci.
- [ ] Cek pesan error/empty state semua halaman.
- [ ] Cek CSV/Excel/PDF export benar format.
- [ ] Update README: credentials (account demo), langkah setup `composer run setup`, troubleshooting MySQL.
- [ ] Update SETUP_COMPLETE.md / COLLABORATION status.

## Acceptance Criteria
- [ ] `php artisan test` hijau
- [ ] Pint clean
- [ ] Semua alur utama berfungsi di manual smoke
- [ ] Dokumen berisi login demo & steps

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