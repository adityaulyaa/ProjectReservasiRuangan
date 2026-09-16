# Pengembangan Platform Khusus 2026 Sebelum UTS
## Project PPK 2026 - Web Platform Sebelum UTS

### Ketentuan Umum
**1. Durasi & Tim**
* Project dikerjakan hingga Ujian Tengah Semester.
* Satu tim terdiri dari 4/5 mahasiswa.
* Setiap anggota tim harus berkontribusi aktif.

**2. Aturan Implementasi**
* **Authentication**: Registrasi, login, logout.
* **Struktur Kode**: Dipisahkan minimal antara bagian koneksi DB, tampilan (HTML), dan logika proses.
* **Validasi Data**: Dilakukan di sisi server dan sisi client untuk form penting.
* **UI/UX**: Tampilan mudah digunakan.

**3. Kolaborasi & Version Control**
* Setiap tim menggunakan GitHub/GitLab repository bersama (wajib).
* Commit harus dilakukan oleh semua anggota, dengan pesan commit yang jelas.
* Minimal ada pembagian folder: `/public`, `/app` (model/controller), `/views`, `/config`.

### Ketentuan Khusus
1. Silakan tambahkan asumsi ataupun tabel/atribut pada rancangan data jika diperlukan.
2. Tugas dikumpulkan maksimal tanggal 11 Oktober 2026 pukul 12.00 WIB Via Kulon.
3. Yang dikumpulkan adalah satu file word berisi:
   * Nama dan NIM anggota kelompok
   * Pembagian tugas
   * Link file program di google drive (source code, sql, dan file-file yang dibutuhkan untuk menjalan program)
   * Informasi setting yang diperlukan untuk menjalankan program
   * Informasi login untuk masing-masing actor/pengguna
   * Screenshoot antar muka dan penjelasan singkat untuk masing-masing fitur
4. Tugas dipresentasikan sebagai bentuk UTS.
5. Jadwal presentasi: TBA.
6. Presentasi mencakup: latar belakang, fitur utama, demo sistem, serta kendala yang dihadapi.
7. Alokasi waktu per kelompok:
   * 10 menit presentasi
   * 10-15 menit tanya jawab

---

## Project: Sistem Reservasi & Pelaporan Fasilitas Kampus
Sebuah aplikasi web untuk mengelola penggunaan fasilitas kampus (ruang kelas, aula, laboratorium, alat, dan lapangan). Pengguna dapat mengecek ketersediaan dan mengajukan reservasi, serta melaporkan kerusakan atau masalah pada fasilitas yang sama. Petugas dan admin memproses kedua alur (reservasi dan laporan) secara terpusat dalam satu sistem.

### Ketentuan Waktu Reservasi
* Jam operasional: 07.00-20.00.
* Reservasi menggunakan slot waktu tetap berdurasi 30 menit (mis. 07.00-07.30, 07.30-08.00, dst.).
* `start_time` dan `end_time` pada reservasi wajib berada dalam rentang jam operasional dan merupakan kelipatan slot 30 menit; validasi ini dilakukan di sisi server, bukan hanya di tampilan kalender.

### Aktor
* **Pengunjung**: bisa melihat daftar fasilitas dan ketersediaan jadwal (tersedia/tidak tersedia), tanpa detail, tanpa login.
* **Pengguna** (mahasiswa/dosen/staf, login): bisa mengajukan reservasi dan melaporkan kerusakan fasilitas.
* **Petugas**: memproses antrian reservasi & laporan yang masuk (approve/reject/cancel reservasi, dan resolusi laporan kerusakan fasilitas), serta memperbarui status ketersediaan fasilitas (termasuk menandai fasilitas dalam perbaikan) berdasarkan laporan yang ditangani.
* **Admin**: mengelola data master fasilitas, melihat rekap lintas fasilitas, mendaftarkan akun petugas dan (jika diperlukan) akun pengguna secara langsung, serta memverifikasi akun pengguna hasil registrasi mandiri.

### User Story

| No | User Story |
|---|---|
| 1 | Sebagai pengunjung/pengguna, saya bisa melihat daftar fasilitas beserta status ketersediaannya per slot waktu (tersedia/tidak tersedia), tanpa melihat detail pemohon atau tujuan penggunaan. |
| 2 | Sebagai pengunjung/pengguna, saya bisa mencari fasilitas berdasarkan tipe/lokasi/kapasitas. |
| 3 | Sebagai pengguna, saya bisa mengajukan reservasi pada rentang waktu tertentu dengan menyebutkan tujuan penggunaan. |
| 4 | Sebagai pengguna, saya bisa membatalkan reservasi saya sendiri sebelum batas waktu tertentu. |
| 5 | Sebagai pengguna, saya bisa melihat riwayat dan status reservasi saya, termasuk detail lengkap reservasi tersebut. |
| 6 | Sebagai pengguna, saya bisa melaporkan kerusakan/masalah pada fasilitas tertentu (kategori, deskripsi, foto). |
| 7 | Sebagai pengguna, saya bisa melihat status laporan saya. |
| 8 | Sebagai petugas, saya bisa melihat dashboard/antrian reservasi dan laporan yang masih menunggu diproses, agar tidak ada yang terlewat. |
| 9 | Sebagai petugas, saya bisa menyetujui/menolak reservasi yang masuk secara manual; sistem mencegah persetujuan reservasi yang bentrok jadwal pada fasilitas yang sama. |
| 10 | Sebagai petugas, saya bisa membatalkan reservasi yang sudah disetujui dalam kondisi mendesak (mis. fasilitas mendadak tidak bisa dipakai), dengan mencantumkan alasan pembatalan. |
| 11 | Sebagai petugas, saya bisa mengubah status laporan (baru/diproses/selesai/ditolak) beserta catatan resolusi saat laporan ditutup. |
| 12 | Sebagai petugas, saya bisa menandai fasilitas berstatus 'dalam perbaikan' terkait laporan kerusakan yang sedang ditangani, dan mengembalikannya ke status aktif setelah selesai diperbaiki. |
| 13 | Sebagai admin, saya bisa mendaftarkan akun petugas secara langsung (petugas tidak melakukan registrasi mandiri dalam kondisi apa pun). |
| 14 | Sebagai admin, saya bisa mendaftarkan akun pengguna (mahasiswa/dosen/staf) secara langsung tanpa melalui form registrasi mandiri. |
| 15 | Sebagai admin, saya bisa memverifikasi atau menolak akun pengguna hasil registrasi mandiri (jika diimplementasikan) sebelum akun tersebut dapat digunakan untuk login. |
| 16 | Sebagai admin, saya bisa mengelola data fasilitas (tambah/edit/nonaktifkan). |
| 17 | Sebagai admin, saya bisa melihat dan mengekspor (CSV/Excel/PDF) rekap okupansi fasilitas dan frekuensi kerusakan per fasilitas/lokasi. |

### Hint Rancangan Database
* **Users** (nama, email, password, role)
* **Facilities** (nama fasilitas, tipe, lokasi, kapasitas, deskripsi)
* **Reservations** (nama pemesan, fasilitas yang dipesan, waktu penggunaan, status)
* **Reports** (pelapor, fasilitas yang dilaporkan, kategori laporan, deskripsi, foto, status laporan)
