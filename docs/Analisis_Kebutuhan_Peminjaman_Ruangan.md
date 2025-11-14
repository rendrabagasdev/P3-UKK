# Analisis Kebutuhan Sistem Peminjaman Ruangan

Versi: 1.0  
Tanggal: 12 November 2025  
Aplikasi: RoomBook (Laravel 12)

---

## 1. Analisis Kebutuhan

### 1.1 Latar Belakang dan Tujuan
- Sistem untuk memesan ruangan secara online, memadukan dua sumber jadwal:
  - Jadwal Reguler: blok waktu tetap yang menutup ruangan pada rentang tanggal tertentu.
  - Booking Pengguna: peminjaman ad‑hoc oleh user pada tanggal/jam spesifik.
- Masalah awal: pengguna bingung melihat sisa slot kosong. Solusi: slot picker interaktif, indikator ketersediaan, legenda status yang jelas, dan penjelasan anti‑ambigu di halaman jadwal.
- Tujuan: mempermudah proses peminjaman, mengurangi konflik jadwal, dan menyediakan laporan operasional yang rapi.

### 1.2 Pemangku Kepentingan (Stakeholders)
- Admin: kelola master (ruangan, pengguna, petugas), memonitor dan mengekspor laporan.
- Petugas: memproses pengajuan (approve/reject), menyelesaikan peminjaman, melihat daftar tugasnya.
- User (peminjam): memilih slot tanggal/jam, membuat booking, mengunduh bukti (PDF), dan melihat riwayat.

### 1.3 Lingkup
- Web app (responsive) berbasis Laravel 12 + Blade + Tailwind/Alpine.
- Database relasional (MySQL/MariaDB).
- Integrasi bukti booking (PDF) dan laporan (filter + ekspor CSV).

---

## 1. Analisis Kebutuhan Fungsional

1. Autentikasi & Otorisasi
   - Login/Logout; role-based access (Admin=1, Petugas=2, User=3).

2. Manajemen Master Data
  - Ruangan: CRUD, kapasitas, fasilitas, status aktif (tanpa harga/biaya).
   - Jadwal Reguler: blokir ruangan pada rentang tanggal.
   - Petugas & User: CRUD dan pengaturan profil dasar.

3. Peminjaman (Booking)
  - User memilih ruangan, tanggal, dan rentang jam (06:00–24:00; multi-jam; toggle batal).
  - Tidak ada biaya/harga. Sistem non-berbayar.
   - Validasi konflik/overlap dengan booking lain dan jadwal reguler.
   - Status alur: proses → diterima/ditolak → selesai.

4. Ketersediaan & Jadwal
   - Endpoint ketersediaan per tanggal (free/occupied/blocked).
   - Kalender (FullCalendar) dan grid mingguan dengan legenda: Proses, Disetujui, Ditolak, Reguler.

5. Konfirmasi & Bukti
   - Halaman konfirmasi setelah simpan booking, tautan WhatsApp admin, unduh bukti PDF.

6. Laporan
  - Filter (tanggal awal/akhir, ruangan, status) dalam kotak persegi rapi.
  - Tabel hasil dan ekspor CSV. Rekap fokus: jumlah booking dan total jam terpakai per ruangan/periode.

7. Notifikasi Ringan
   - Tautan WA siap kirim; (opsional) email/telegram di fase berikutnya.

---

## 1.2 Analisis Kebutuhan Fitur (Detail)

- Slot Picker Interaktif
  - Date pills 14 hari ke depan; grid jam 06–24; pilih rentang jam dengan klik berurutan; unselect dengan klik ulang.
  - Label "Tidak tersedia" mengganti "Penuh"; fallback jika fetch gagal.

- Non-Biaya (Tanpa Pricing)
  - Tidak ada perhitungan harga, segmentasi harga, maupun override weekend. Semua peminjaman bersifat gratis.

- Kalender Terintegrasi
  - FullCalendar Bulan/Minggu/Hari/List, badge/label status di event, penjelasan anti‑ambigu di atas kalender.

- PDF Bukti Booking
  - Dompdf, template A4 portrait; siap unduh; dapat ditambah logo/QR/tanda tangan.

- Laporan Operasional
  - Filter dengan kotak persegi panjang seragam; hasil tabel; ekspor CSV satu klik.

- Pengelolaan Peran
  - Admin mengelola master dan laporan; Petugas memproses booking; User melakukan booking.

---

## 1.3 Analisis Kebutuhan Hardware & Software

### Perangkat Keras (Server)
- CPU: 2 vCPU atau lebih; RAM: 2–4 GB; Storage: ≥20 GB; OS: Ubuntu 22.04 LTS.
- Web server: Nginx/Apache + PHP‑FPM 8.2; Database: MySQL/MariaDB 10.6+; Composer.

### Perangkat Keras (Client)
- Browser modern (Chrome/Edge/Firefox terbaru), resolusi minimal 1366×768.

### Perangkat Lunak (Stack)
- Backend: PHP 8.2, Laravel 12.
- DB: MySQL/MariaDB.
- Frontend: Blade, Tailwind CSS, Alpine.js, FullCalendar (CDN), Font Awesome.
- PDF: dompdf/dompdf 2.0.7.
- Auth & Tooling: Laravel Sanctum; PHPUnit 11 untuk pengujian.
- Dev (Windows): Laragon/XAMPP, Composer, Node (jika build aset diperlukan).

---

## 2. Analisis Kebutuhan Software (Desain Teknis)

### 2.1 Arsitektur
- Pola MVC (Laravel), routing web + middleware auth; helper/Service akses peran (RoleAccessService).
- Tiga peran akses; guard validasi di controller.

### 2.2 Model & Relasi Inti
- User (id_user, username, nama, role, …) hasOne Petugas.
- Petugas (id_petugas, nama_petugas, id_user, no_hp) hasMany Booking.
- Room (id_room, nama_room, kapasitas, fasilitas, status_aktif, lokasi, deskripsi) hasMany Booking dan JadwalReguler.
- Booking (id_booking, id_user, id_petugas, id_room, tanggal_mulai/selesai, status, durasi, keterangan) belongsTo User/Petugas/Room.
- JadwalReguler (id_reguler, id_room, tanggal_mulai/selesai, keterangan) belongsTo Room.

### 2.3 Aturan Bisnis
- Jam operasional: 06:00–24:00 (konfigurable).
- Overlap check: booking baru ditolak jika beririsan booking aktif (proses/diterima) atau tertutup Jadwal Reguler.
- Tidak ada perhitungan harga. Validasi fokus pada ketersediaan dan anti-overlap.
- Transisi status diawasi role; hanya petugas yang bisa approve/reject/selesai.

### 2.4 Endpoint & Alur
- GET `/user/slot-booking/available?id_room&tanggal` → JSON: free/occupied/blocked.
- Alur booking: pilih slot → form → POST booking → redirect ke halaman konfirmasi → unduh PDF / kontak WA.

### 2.5 Antarmuka Pengguna
- Slot picker (date pills, grid jam), form booking sederhana (“Nama Penyewa”), halaman konfirmasi (WA + PDF).
- Kalender jadwal dengan legenda dan label status (anti‑ambigu).
- Laporan dengan filter boxed dan ekspor CSV.

### 2.6 Keamanan, Kinerja, Uji
- Keamanan: middleware auth; validasi server-side (tanggal/jam/id_room/durasi>0); role check.
- Kinerja: query berindeks (id_room, tanggal_mulai/selesai), cache dapat ditambahkan; pagination untuk laporan.
- Uji: Unit (cek utilitas waktu/anti-overlap); Feature (available endpoint, booking store overlap & multi-jam, akses peran).

### 2.7 Deploy & Operasional
- ENV: DB_*, APP_URL, WHATSAPP_NUMBER, timezone.
- Optimize: config/route/view cache; rotasi log; backup basis data berkala.

---

## Lampiran (Opsional)
- Diagram Use Case singkat (Admin/Petugas/User).
- ERD sederhana (User—Petugas—Booking—Room—JadwalReguler).
- Contoh PDF bukti booking dan CSV laporan.
