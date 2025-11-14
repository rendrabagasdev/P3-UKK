# Deploy Gratis Laravel (RoomBook) ke Fly.io

Tujuan: Menyediakan hosting gratis (cukup untuk ujian UKK) dengan SSL otomatis, akses console (mirip SSH), dan langkah cepat.

## 1. Prasyarat
- Akun Fly.io (daftar di https://fly.io)
- Fly CLI terpasang
- APP_KEY Laravel (bisa generate nanti)
- Jika perlu database produksi: gunakan MySQL/Postgres eksternal (Railway / Neon / PlanetScale) atau Fly Postgres.

## 2. Install Fly CLI (Windows PowerShell)
```powershell
iwr https://fly.io/install.ps1 -UseBasicParsing | iex
fly auth login
```

## 3. Tambah Dockerfile (Minimal) di root proyek
```Dockerfile
FROM php:8.2-fpm-alpine
RUN apk add --no-cache bash git curl libpq-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php \
    && php composer.phar install --no-dev --prefer-dist --no-scripts --no-interaction
COPY . .
# Generate key (akan overwrite kalau sudah ada)
RUN php artisan key:generate || true
EXPOSE 8080
CMD ["php","artisan","serve","--host=0.0.0.0","--port=8080"]
```

Catatan: Untuk produksi serius sebaiknya pisahkan Nginx + PHP-FPM, ini cukup untuk demo UKK.

## 4. Launch Aplikasi
```powershell
fly launch --now
```
Jawab: gunakan Dockerfile, pilih region terdekat. Setelah selesai akan muncul URL *.fly.dev.

## 5. Set Secrets (ENV)
Jika pakai database eksternal (contoh MySQL):
```powershell
fly secrets set APP_KEY="base64:ISI_KEY" `
  DB_CONNECTION=mysql DB_HOST=host.example.com DB_PORT=3306 `
  DB_DATABASE=db_name DB_USERNAME=db_user DB_PASSWORD=db_pass
```
Jika tetap pakai SQLite, bisa copy file database ke image (kurang ideal untuk multi-container; disarankan pindah ke Postgres / MySQL).

Generate APP_KEY kalau belum:
```powershell
fly ssh console
php artisan key:generate --force
```
Salin nilai yang muncul dan set ulang `APP_KEY` di secrets jika perlu.

## 6. Migrasi & Seed
```powershell
fly ssh console
php artisan migrate --force --ansi
php artisan db:seed --force --ansi
```
Akun siap:
- admin / admin123 (role 1)
- petugas / petugas123 (role 2)
- user / user123 (role 3)

## 7. Verifikasi Fungsional
Checklist penguji:
| Item | Status |
|------|--------|
| Login semua role | ✅ |
| Tambah booking (harga = 0) | ✅ |
| Overlap ditolak | ✅ |
| Cetak PDF bukti booking | ✅ |
| Logout aman | ✅ |
| Tidak ada error harga | ✅ |

## 8. Queue (Opsional)
Set dulu mode sinkron agar ringan:
```
QUEUE_CONNECTION=sync
```
Jika butuh worker terpisah:
- Gunakan Machines / process kedua dengan command: `php artisan queue:listen --tries=1`.

## 9. Tips Troubleshooting
| Gejala | Solusi Cepat |
|--------|--------------|
| 500 error setelah deploy | Cek APP_KEY / migrasi belum jalan |
| PDF error Dompdf | Pastikan dependency ada (sudah di composer) |
| Booking tidak tersimpan | Pastikan tabel `booking` sudah migrate & foreign key valid |
| Kolom harga muncul | Cache view lama, jalankan `php artisan view:clear` di console |
| Peran tidak sesuai | Cek seeder atau isi tabel `user` |

## 10. Keamanan Minimal
- Jangan commit `.env`.
- Gunakan secrets Fly.io untuk semua kredensial.
- Nonaktifkan debug: `APP_DEBUG=false` di secrets.
- Pastikan hanya port 8080 yang diekspos (fly environment handle HTTPS otomatis).

## 11. Alternatif Jika Fly.io Down
| Platform | Kelebihan | Kekurangan |
|----------|-----------|------------|
| Railway | Deploy super cepat | Bukan raw SSH (CLI exec saja) |
| Koyeb | Simpel 1 service | Tidak raw SSH penuh |
| Oracle Free | VM penuh | Setup Nginx + SSL manual lebih lama |

## 12. Mode Gratis (Harga = 0)
Semua field harga tetap ada (migrations) tapi nilai dipaksa 0 di controller/model. Penguji tidak perlu input harga.

## 13. Penguji Langsung
URL contoh: `https://your-app-name.fly.dev`
Berikan daftar credential dan catatan bahwa sistem non-billing.

## 14. Langkah Cepat Super Singkat
```powershell
fly auth login
fly launch --now
fly ssh console
php artisan key:generate --force
php artisan migrate --force --ansi
php artisan db:seed --force --ansi
```
Selesai.

Selamat ujian UKK! Semoga lolos tanpa drama hosting.
