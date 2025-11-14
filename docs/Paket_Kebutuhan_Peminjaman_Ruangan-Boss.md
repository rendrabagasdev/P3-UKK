# Paket Kebutuhan Peminjaman Ruangan (Ringkas untuk Pimpinan)

## 1. Analisis Kebutuhan
### 1.1 Analisis Kebutuhan Fungsional
Sistem Peminjaman Ruangan mendukung pengelolaan ruangan, jadwal reguler, booking, laporan, konfirmasi, dan role berbasis akses. Sistem bersifat non-berbayar (tanpa segmentasi harga/biaya).

1. Autentikasi
- Login, logout, sesi aman.
- Role: Admin, Petugas, User.

2. Manajemen User
- Admin ubah role (User -> Petugas -> Admin).
- CRUD user (nama, username, email, password, role, no_telepon opsional).

3. Manajemen Ruangan
- CRUD ruangan (nama unik, kapasitas, fasilitas, status aktif) — tanpa kolom harga.

4. Jadwal Reguler
- Blokir ruangan pada rentang tanggal/jam.
- Menutup slot agar tak bisa dibooking.
- Simpan keterangan blokir.

5. Booking
- Pilih ruangan, tanggal, jam (06:00-24:00; multi-jam).
- Slot picker interaktif (start-end dengan klik berurutan, klik ulang batal).
- Validasi anti-overlap (booking aktif + Jadwal Reguler).
- Tanpa harga/biaya. Status: Diajukan/Proses -> Diterima/Ditolak -> Selesai / Batal.
- Nomor/kode booking unik.

6. Ketersediaan & Kalender
- Endpoint ketersediaan per tanggal (free/occupied/blocked).
- Kalender Bulan/Minggu/Hari/List: Booking + Reguler (badge warna + label).
- Panel penjelasan + legenda anti-ambigu.

7. Konfirmasi & Bukti
- Halaman konfirmasi: WhatsApp, Cetak, PDF.
- Template PDF A4 siap branding (logo/QR/tanda tangan).

8. Laporan
- Filter boxed (tanggal awal/akhir, ruangan, status).
- Tabel hasil + ekspor CSV (opsional PDF).
- Rekap: total booking dan jam terpakai per ruangan/periode (tanpa pendapatan).

9. Notifikasi
- Tautan WA siap kirim (payload format teks).
- (Opsional fase lanjut) email/telegram.

10. Pengaturan Aplikasi
- Jam operasional (default 06:00-24:00), timezone, nomor WhatsApp.
- Branding (nama aplikasi, warna primer).

### 1.2 Analisis Kebutuhan Fitur
1. Slot Picker Interaktif
- Date pills 14 hari ke depan; grid jam 06-24; pilih rentang; fallback jika gagal fetch.

2. Non-Pricing
- Tidak ada perhitungan harga; semua peminjaman gratis.

3. Kalender Terintegrasi
- Tampilan fleksibel; badge status; panel info.

4. PDF Bukti Booking
- Dompdf A4 portrait; siap elemen visual.

5. Laporan Operasional
- Filter seragam; CSV 1 klik; (opsional) rekap cepat.

6. Pengelolaan Peran
- Admin: master + laporan.
- Petugas: proses booking.
- User: buat booking + unduh bukti.

### 1.3 Kebutuhan Hardware & Software
- Server: 2 vCPU, RAM 2–4 GB, Storage >= 20 GB, Ubuntu 22.04, Nginx/Apache + PHP-FPM 8.2, MySQL/MariaDB 10.6+, Composer.
- Client: Browser modern >= 1366x768.
- Stack: PHP 8.2, Laravel 12, Blade, Tailwind, Alpine.js, FullCalendar CDN, dompdf 2.0.7, Sanctum, PHPUnit 11.

## 2. Desain Teknis (Ringkas)
### 2.1 Arsitektur
MVC Laravel; middleware auth; RoleAccessService.

### 2.2 Relasi Inti
- User hasOne Petugas.
- Petugas hasMany Booking.
- Room hasMany Booking & JadwalReguler.
- Booking belongsTo User/Petugas/Room.
- JadwalReguler belongsTo Room.

### 2.3 Aturan Bisnis
- Jam operasional: 06:00-24:00 (konfigurable global).
- Overlap ditolak (booking aktif + jadwal reguler).
- Tidak ada perhitungan harga.
- Status transisi oleh Petugas (audit siapa & kapan).

### 2.4 Endpoint & Alur Inti
- GET /user/slot-booking/available?id_room&tanggal -> JSON slot status.
- Alur: pilih slot -> form -> POST -> konfirmasi -> WA/Print/PDF.

### 2.5 UI Utama
Slot picker, form booking, kalender + legenda, laporan (filter boxed), konfirmasi.

### 2.6 Keamanan, Kinerja, Uji
- Middleware auth + role check.
- Indeks (id_room, tanggal_mulai, tanggal_selesai).
- Unit test (utilitas waktu/overlap), feature test (availability & overlap multi-jam).

### 2.7 Deploy & Operasional
ENV (DB_*, APP_URL, WHATSAPP_NUMBER, timezone); cache optimize; rotasi log; backup DB.

## 3 Definisi Sukses (KPI Ringkas)
- <5% booking gagal akibat overlap tak terdeteksi.
- Load kalender <2 detik pada 1000 event/bulan.
- >80% booking sukses menggunakan PDF/WA.

## 4 Batasan & Asumsi
- Jam operasional global (belum per ruangan di fase awal).
- Tidak ada pembayaran online; sistem non-berbayar.
- Notifikasi resmi (email/telegram) fase lanjut.

## 5 Risiko
- Jadwal reguler kurang lengkap -> konflik.
- Zona waktu beda (server vs klien).
- CSS Dompdf terbatas (perlu inline style penting).

---
Ringkas ini siap dipresentasikan atau disalin ke Google Docs.
