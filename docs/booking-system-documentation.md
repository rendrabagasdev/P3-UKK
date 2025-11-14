# Dokumentasi Sistem Booking Ruangan

Tanggal: 12 Nov 2025
Repositori: `P3-UKK`
Teknologi utama: Laravel 12 (PHP 8.2), Blade, Tailwind CSS, Alpine.js, FullCalendar 6 (CDN), dompdf/dompdf 2.0.7, MySQL/MariaDB

## Ringkasan Eksekutif
Sistem ini menyediakan pemesanan ruangan dengan slot waktu yang jelas, konfirmasi via WhatsApp, halaman cetak yang ramah printer, serta unduh PDF. Admin dan Petugas dapat mengelola jadwal reguler, booking, ruangan, dan petugas. Laporan memiliki filter yang rapi (boxed UI), dan kalender menampilkan perbedaan antara “Jadwal Reguler” vs “Booking”. Sistem ini bersifat non-berbayar (tanpa kalkulasi harga).

## Tujuan & Ruang Lingkup
- Memastikan ketersediaan slot yang eksplisit (no-overlap) saat booking.
- Menyediakan alur pemesanan multi-langkah dengan konfirmasi WA, cetak, dan PDF.
- Non-berbayar: tidak ada fitur pricing/pendapatan.
- Menyediakan halaman jadwal yang mudah dipahami (legenda + label event).
- Fitur admin/petugas: manajemen ruangan, jadwal reguler, booking, petugas, laporan.

## Peran dan Akses
- Admin (role_id = 1): Akses penuh seluruh modul.
- Petugas (role_id = 2): Pengelolaan harian (booking, jadwal, laporan operasional).
- User (role_id = 3): Melakukan booking.

Layanan peran didelegasikan oleh `app/Services/RoleAccessService.php`.

## Model Data (ringkas)
- `User`: autentikasi pengguna. Penambahan kolom `nama` (migration `add_nama_to_user_table`).
- `Room`: ruangan. Nama unik (migration `add_unique_index_to_room_name_and_deduplicate`). Tanpa kolom harga; fokus pada kapasitas/fasilitas/status.
- `JadwalReguler`: jadwal blokir berulang/terjadwal untuk ruangan.
- `Booking`: pemesanan. Penambahan kolom waktu & tipe (migration `add_time_and_type_to_booking_table`), serta `alasan_tolak` (jika ditolak).
- `Petugas`: data petugas, termasuk `no_hp` (migration `add_no_hp_to_petugas_table`).

Catatan: Detail kolom dapat dicek via migration di folder `database/migrations/`.

## Catatan Non-Biaya
- Tidak ada segmentasi harga, override akhir pekan, ataupun estimasi/total biaya. Semua peminjaman gratis.

## Logika Ketersediaan & Anti-Overlap
- Slot dihitung terhadap rentang waktu yang dipilih dan dibandingkan dengan:
  - Booking lain (status aktif/tertentu).
  - Jadwal Reguler (blokir terencana) untuk ruangan yang sama.
- Endpoint ketersediaan mengembalikan status: `free`, `occupied`, `blocked` per slot.
- Lihat juga catatan overlap di `TEST_BOOKING_OVERLAP.md`.

## Alur Pemesanan (User)
1. Pilih ruangan, tanggal, dan rentang waktu pada Slot Picker.
2. Sistem menampilkan ketersediaan (tanpa estimasi harga).
3. Isi data pemesan dan submit.
4. Halaman konfirmasi:
   - Tombol WhatsApp untuk kirim konfirmasi ke nomor pemesan/pengelola.
   - Tombol Cetak (print-optimized CSS).
   - Tombol Unduh PDF.

## Kalender Jadwal
- Menggunakan FullCalendar 6 (CDN) pada halaman jadwal.
- Panel informasi menjelaskan perbedaan “Jadwal Reguler” vs “Booking”.
- Label/Tag pada event memperjelas jenis event.
- Legend diperluas untuk mengurangi ambiguitas.

## Konfirmasi WhatsApp
- Tautan WA dibentuk dengan payload teks berisi ringkasan pemesanan (kode, ruangan, tanggal, waktu) tanpa informasi harga.
- Format umum: `https://wa.me/<no_tujuan>?text=<pesan-terencode>`.

## PDF: Implementasi & Troubleshooting
- Paket yang digunakan: `dompdf/dompdf` v2.0.7 (langsung), bukan wrapper `barryvdh/laravel-dompdf` (inkompatibel dengan Laravel 12).
- Controller: `app/Http/Controllers/Web/SlotBookingController.php` method `confirmPdf()` merender Blade → HTML → Dompdf → response PDF (headers diset manual).
- View PDF: `resources/views/user/slot-booking/confirm-pdf.blade.php`.
- Catatan penting:
  - Jangan daftarkan DomPDF Service Provider (wrapper) di `bootstrap/providers.php`.
  - Jika tampilan kosong atau CSS tidak ter-load: gunakan CSS inline atau file absolut (Dompdf membatasi resource eksternal). Pertimbangkan inlining style penting.
  - Gunakan encoding UTF-8 dan font yang mendukung karakter lokal.

## Modul Petugas (Staff)
- Form tambah/edit Petugas diselaraskan dengan skema: `username`, `nama_petugas`, `no_hp` (email opsional), password terkonfirmasi.
- Saat create:
  - Membuat `User` terkait (username, nama, no_telepon).
  - Membuat entri `Petugas` (nama_petugas, no_hp).
- Perubahan skema: migration `add_no_hp_to_petugas_table`. Model `Petugas` menambahkan `no_hp` pada `$fillable`.
- View: `resources/views/staff/create.blade.php`, `edit.blade.php`.

## Laporan
- Halaman filter laporan ditata dalam kotak (boxed rectangular inputs) untuk keterbacaan.
- Bisa dikembangkan: ringkasan total, unduh CSV/PDF.

## Endpoint & Route (ringkas)
- Web routes terkait Slot Booking (nama method dapat berbeda sesuai implementasi):
  - `form()` → menampilkan form pemilihan slot.
  - `available()` → JSON ketersediaan slot (alias param didukung).
  - `store()` → simpan booking; redirect ke konfirmasi.
  - `confirm()` → halaman konfirmasi + link WA + tombol cetak.
  - `confirmPdf()` → respons unduhan PDF.
- Lihat definisi aktual di `routes/web.php` dan jalankan daftar route untuk memastikan nama/URL.

Jalankan daftar route (opsional, Windows PowerShell):
```powershell
php artisan route:list
```

## Instalasi & Menjalankan (Laragon/Windows)
1. Salin `.env` dan set koneksi database (MySQL/MariaDB).
2. Install dependency Composer:
   ```powershell
   composer install
   ```
3. Generate app key:
   ```powershell
   php artisan key:generate
   ```
4. Migrasi database (dan seeding jika tersedia):
   ```powershell
   php artisan migrate
   # php artisan db:seed   # opsional jika ada seeder
   ```
5. Jalankan server dev (jika tidak memakai Laragon auto-virtualhost):
   ```powershell
   php artisan serve
   ```

Cache clear (jika terjadi inkonsistensi):
```powershell
php artisan optimize:clear
```

## Pengujian
- Kerangka: PHPUnit 11.
- Contoh fokus uji: overlap booking, validasi ketersediaan, dan role access.
- Menjalankan tes:
```powershell
php artisan test
# atau
vendor\bin\phpunit
```

## Keamanan & Validasi
- Validasi input: tanggal, waktu, ruangan, konflik jadwal.
- Sanitasi: hindari injection pada query/filter laporan.
- Auth: Laravel Sanctum; batasi aksi admin/petugas.
- PDF: hindari memuat resource eksternal yang tidak terpercaya.

## Pemecahan Masalah Umum
- PDF error “Class not found” Dompdf: pastikan `dompdf/dompdf` terpasang dan TIDAK memakai provider wrapper.
- PDF tampilan berantakan: inlining CSS penting; gunakan ukuran kertas A4 dan margin eksplisit.
- Tidak bisa tambah Petugas: pastikan migration `add_no_hp_to_petugas_table` sudah dijalankan; field form sesuai controller.
- Jadwal ambigu: gunakan halaman jadwal terbaru (ada panel info + label event + legenda diperluas).

## Roadmap Singkat
- Branding PDF (logo, QR, tanda-tangan digital).
- Ringkasan di Laporan (total, rekap per ruangan/periode) + ekspor CSV/PDF.
- Notifikasi tambahan (email/push) bila diperlukan.
- Health checks dan audit trail.

## Perubahan Terakhir (Changelog ringkas)
- PDF: beralih ke `dompdf/dompdf` 2.0.7; update `confirmPdf()`; view PDF khusus.
- Staff: perbaikan flow tambah/edit; tambah kolom `no_hp`; update model + view.
- Laporan: filter dibungkus kotak.
- Jadwal: panel penjelasan + label event + legenda diperluas.

## Lampiran
- File kunci:
  - Controller: `app/Http/Controllers/Web/SlotBookingController.php`
  - Model: `app/Models/{Booking, Room, JadwalReguler, Petugas, User}.php`
  - View PDF: `resources/views/user/slot-booking/confirm-pdf.blade.php`
  - Jadwal: `resources/views/schedule/index.blade.php`
  - Laporan: `resources/views/reports/index.blade.php`
  - Migrasi terkait: lihat folder `database/migrations/`

---
Jika butuh versi singkat untuk dibagikan, salin bagian “Ringkasan Eksekutif”, “Alur Pemesanan”, dan “PDF: Implementasi & Troubleshooting”.
