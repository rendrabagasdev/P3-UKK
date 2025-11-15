# Panduan Konfigurasi Sanctum Token Authentication

## Perubahan yang Dilakukan

Aplikasi telah diupdate dari session-based authentication ke Sanctum token-based authentication untuk mengatasi masalah auto-redirect saat deployment.

### Masalah yang Diperbaiki

- Session tidak berfungsi saat deploy (auto redirect ke login)
- Cookie session tidak bisa disimpan karena masalah HTTPS/domain
- Dependency pada database session yang tidak reliable

### Solusi Implementasi

- Menggunakan Laravel Sanctum untuk token authentication
- Token disimpan di secure HTTP-only cookie
- Tidak bergantung pada session database

## File yang Diubah

1. **app/Http/Controllers/Web/AuthController.php**

   - Login menggunakan Sanctum token
   - Token disimpan di cookie dengan expire 7 hari
   - Logout menghapus semua token user

2. **app/Http/Middleware/AuthenticateWithToken.php** (BARU)

   - Middleware custom untuk autentikasi menggunakan token dari cookie
   - Mengambil token dari cookie dan validasi

3. **bootstrap/app.php**

   - Mengganti middleware 'auth' dari Authenticate ke AuthenticateWithToken

4. **.env.example**
   - Update konfigurasi session untuk deployment

## Konfigurasi Environment untuk Deployment

Pastikan file `.env` di server production memiliki konfigurasi berikut:

```env
# Session Configuration
SESSION_DRIVER=cookie
SESSION_LIFETIME=10080
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Sanctum Configuration (opsional, sesuaikan dengan domain production)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
```

## Cara Deploy

1. **Push perubahan ke repository**

   ```bash
   git add .
   git commit -m "Migrate to Sanctum token authentication"
   git push
   ```

2. **Di server production, jalankan:**

   ```bash
   # Pull perubahan terbaru
   git pull origin main

   # Update dependencies (jika diperlukan)
   composer install --optimize-autoloader --no-dev

   # Clear cache
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear

   # Optimize untuk production
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Update file .env di production**
   - Pastikan `SESSION_DRIVER=cookie`
   - Pastikan `SESSION_SECURE_COOKIE=true` jika menggunakan HTTPS
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`

## Testing

### Local Testing

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Test login
# Buka browser dan coba login
```

### Production Testing

1. Login ke aplikasi
2. Refresh halaman - seharusnya tetap login
3. Close browser dan buka kembali - seharusnya tetap login (sampai 7 hari)
4. Logout dan pastikan redirect ke halaman login

## Troubleshooting

### Masalah: Tetap redirect ke login setelah login

**Solusi:**

- Pastikan cookies enabled di browser
- Cek `SESSION_SECURE_COOKIE` - set `false` jika tidak menggunakan HTTPS
- Clear browser cookies
- Pastikan token table exists: `php artisan migrate`

### Masalah: Token tidak tersimpan

**Solusi:**

- Pastikan tabel `personal_access_tokens` sudah ada
- Run migration: `php artisan migrate`
- Clear config cache: `php artisan config:clear`

### Masalah: CORS error

**Solusi:**

- Update `SANCTUM_STATEFUL_DOMAINS` di .env dengan domain production
- Pastikan domain sesuai dengan yang diakses user

## Keamanan

- Token disimpan di HTTP-only cookie (tidak bisa diakses JavaScript)
- Cookie secure untuk HTTPS
- Token auto-expire setelah 7 hari
- Logout menghapus semua token user
- SameSite protection untuk mencegah CSRF

## Catatan Penting

1. **Tidak perlu tabel sessions lagi** - bisa dihapus jika mau, tapi tidak wajib
2. **Token lifetime** - default 7 hari, bisa diubah di AuthController.php line 53
3. **Multiple device login** - setiap login menghapus token lama (single session)
4. **API tetap menggunakan Sanctum** - tidak ada perubahan pada API routes
