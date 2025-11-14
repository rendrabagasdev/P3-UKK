<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\PetugasBookingController;
use App\Http\Controllers\Api\SlotBookingController;
use Illuminate\Support\Facades\Route;

// Handle preflight OPTIONS requests
Route::options('{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| Rute API
|--------------------------------------------------------------------------
|
| Di sinilah Anda dapat mendaftarkan rute API untuk aplikasi Anda.
| Rute-rute ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditetapkan ke grup middleware "api". Buat sesuatu yang hebat!
|
*/

// Rute publik dengan rate limiting (60 requests per menit)
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // Pendaftaran
    Route::post('/login', [AuthController::class, 'login']); // Masuk

    // Rute ruangan publik
    Route::get('/rooms', [RoomController::class, 'index']); // Daftar ruangan
    Route::get('/rooms/{id}', [RoomController::class, 'show']); // Detail ruangan
    Route::get('/slot-rooms', [SlotBookingController::class, 'getRooms']); // Daftar ruangan dengan slot pricing
});

// Rute terproteksi - Menggunakan middleware auth:sanctum + throttle (60 requests per menit)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Rute autentikasi
    Route::post('/logout', [AuthController::class, 'logout']); // Keluar
    Route::get('/profile', [AuthController::class, 'profile']); // Profil pengguna
    Route::put('/profile', [AuthController::class, 'updateProfile']); // Update profil
    Route::put('/change-password', [AuthController::class, 'changePassword']); // Ubah password

    // Rute ruangan
    Route::get('/rooms/{id}/check-availability', [RoomController::class, 'checkAvailability']); // Cek ketersediaan

    // Rute peminjaman
    Route::get('/bookings', [BookingController::class, 'index']); // Daftar peminjaman
    Route::post('/bookings', [BookingController::class, 'store']); // Buat peminjaman
    Route::get('/bookings/{id}', [BookingController::class, 'show']); // Detail peminjaman
    Route::put('/bookings/{id}', [BookingController::class, 'update']); // Perbarui peminjaman
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']); // Batalkan peminjaman

    // Rute slot booking (sistem baru)
    Route::post('/slot-bookings', [SlotBookingController::class, 'store']); // Booking slot
    Route::get('/my-slot-bookings', [SlotBookingController::class, 'myBookings']); // Booking saya

    // Rute petugas (role=2)
    Route::prefix('petugas')->group(function () {
        Route::get('/bookings', [PetugasBookingController::class, 'index']); // Daftar peminjaman yang ditangani petugas
        Route::put('/bookings/{id}/approve', [PetugasBookingController::class, 'approve']); // Setujui peminjaman
        Route::put('/bookings/{id}/reject', [PetugasBookingController::class, 'reject']); // Tolak peminjaman
        Route::put('/bookings/{id}/complete', [PetugasBookingController::class, 'complete']); // Tandai selesai
    });
});
