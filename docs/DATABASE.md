# Database Schema Documentation

## Overview

Sistem Reservasi & Pelaporan Fasilitas Kampus menggunakan MySQL 8.0+ dengan 6-8 tabel utama.

## Tabel Utama

### 1. users
```
- id (bigint, PK)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string, hashed)
- role (enum: 'admin', 'staff', 'user')
- is_verified (boolean, default: false)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)
```

**Indexes:**
- email (unique)
- role
- is_verified

---

### 2. facilities
```
- id (bigint, PK)
- name (string)
- type (string) - ruang kelas, lab, aula, lapangan, dll
- location (string)
- capacity (integer)
- description (text, nullable)
- status (enum: 'active', 'maintenance', 'inactive')
- created_at, updated_at (timestamps)
```

**Indexes:**
- type
- status
- location

---

### 3. reservations
```
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- facility_id (bigint, FK → facilities.id)
- start_time (datetime)
- end_time (datetime)
- purpose (string)
- status (enum: 'pending', 'approved', 'rejected', 'cancelled')
- rejection_reason (text, nullable)
- cancellation_reason (text, nullable)
- created_at, updated_at (timestamps)
```

**Indexes:**
- user_id
- facility_id
- (facility_id, start_time, end_time) - untuk conflict check
- status

**Constraints:**
- start_time < end_time
- end_time - start_time = 30 menit (multiple of 30)
- start_time >= 07:00, end_time <= 20:00
- Unique: (facility_id, start_time, end_time) untuk approved reservations

---

### 4. reports
```
- id (bigint, PK)
- user_id (bigint, FK → users.id)
- facility_id (bigint, FK → facilities.id)
- category (string) - rusak, kotor, tidak berfungsi, dll
- description (text)
- photo_path (string, nullable)
- status (enum: 'new', 'in_progress', 'resolved', 'rejected')
- resolution_notes (text, nullable)
- created_at, updated_at (timestamps)
```

**Indexes:**
- user_id
- facility_id
- status
- created_at

---

### 5. reservation_logs
```
- id (bigint, PK)
- reservation_id (bigint, FK → reservations.id)
- action (string) - created, approved, rejected, cancelled
- notes (text, nullable)
- created_by_id (bigint, FK → users.id) - staff/admin yang process
- created_at (timestamp)
```

**Indexes:**
- reservation_id
- created_by_id
- action

---

### 6. report_logs
```
- id (bigint, PK)
- report_id (bigint, FK → reports.id)
- status_before (string)
- status_after (string)
- notes (text, nullable)
- created_by_id (bigint, FK → users.id) - staff/admin yang process
- created_at (timestamp)
```

**Indexes:**
- report_id
- created_by_id
- created_at

---

## Relasi (Relationships)

```
User (1) ──→ (many) Reservations
User (1) ──→ (many) Reports
User (1) ──→ (many) ReservationLogs (sebagai updater)
User (1) ──→ (many) ReportLogs (sebagai updater)

Facility (1) ──→ (many) Reservations
Facility (1) ──→ (many) Reports

Reservation (1) ──→ (many) ReservationLogs
Report (1) ──→ (many) ReportLogs
```

## Enums

### Role
- `admin` - Kelola semua aspek sistem
- `staff` - Proses reservasi & laporan
- `user` - Pengguna reguler (mahasiswa/dosen/staf)

### ReservationStatus
- `pending` - Menunggu approval
- `approved` - Disetujui
- `rejected` - Ditolak
- `cancelled` - Dibatalkan

### ReportStatus
- `new` - Baru dilaporkan
- `in_progress` - Sedang diproses
- `resolved` - Selesai
- `rejected` - Ditolak

### FacilityStatus
- `active` - Tersedia untuk reservasi
- `maintenance` - Sedang perbaikan
- `inactive` - Tidak aktif

## Migration Strategy

**Order untuk migration:**
1. Create users table (tanpa FK)
2. Create facilities table
3. Create reservations table (dengan FK user & facility)
4. Create reports table (dengan FK user & facility)
5. Create reservation_logs table
6. Create report_logs table

**Setiap migration:**
- Gunakan `$table->timestamps()` untuk created_at & updated_at
- Gunakan `$table->softDeletes()` jika perlu soft delete
- Add indexes untuk semua FK & frequently queried columns
- Add foreign key constraints dengan `onDelete('cascade')`

## Seeding Data

**Demo Data untuk Development:**
- 1 Admin user (email: admin@test.com)
- 3 Staff users
- 5 Regular users (mahasiswa/dosen)
- 10 Facilities (berbagai tipe)
- 20 Sample reservations
- 10 Sample reports

Lihat `database/seeders/` untuk implementation details.

## Query Optimization

**Frequently Used Queries:**
1. Get available slots untuk facility pada tanggal tertentu
2. Check reservation conflicts
3. Get user's reservations & reports
4. Get pending reservations/reports untuk staff

**Optimization:**
- Eager load relationships (Eloquent with() method)
- Add indexes untuk commonly filtered columns
- Use query builder select() untuk specific columns
- Cache facility list (rarely changes)
