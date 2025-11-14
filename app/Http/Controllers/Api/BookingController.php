<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Menampilkan daftar peminjaman pengguna.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $bookings = Booking::where('id_user', $user->id)
            ->with('room')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman berhasil diambil',
            'data' => $bookings,
        ]);
    }

    /**
     * Menyimpan peminjaman baru.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Log request untuk debugging
            \Log::info('Booking request received', $request->all());
            
            // Get authenticated user
            $user = Auth::user();
            \Log::info('Authenticated user', ['user_id' => $user->id, 'username' => $user->username]);

            $validator = \Validator::make($request->all(), [
                'id_room' => 'required|integer|exists:room,id_room',
                'tipe_booking' => 'required|in:hourly,daily',
                // Harga tidak diperlukan karena gratis
                'durasi' => 'required|integer|min:1',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'keterangan' => 'required|string|max:500',
            ], [
                'id_room.required' => 'Ruangan harus dipilih',
                'id_room.exists' => 'Ruangan tidak ditemukan',
                'tipe_booking.required' => 'Tipe booking harus dipilih',
                'tipe_booking.in' => 'Tipe booking tidak valid',
                'durasi.required' => 'Durasi harus diisi',
                'tanggal_mulai.required' => 'Tanggal mulai harus diisi',
                'tanggal_mulai.date' => 'Tanggal mulai tidak valid',
                'tanggal_selesai.required' => 'Tanggal selesai harus diisi',
                'tanggal_selesai.date' => 'Tanggal selesai tidak valid',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
                'keterangan.required' => 'Keterangan harus diisi',
                'keterangan.max' => 'Keterangan maksimal 500 karakter',
            ]);

            if ($validator->fails()) {
                \Log::warning('Booking validation failed', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $room = Room::find($request->id_room);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak ditemukan',
                ], 404);
            }

            // Memeriksa ketersediaan ruangan
            \Log::info('Checking room availability', [
                'room_id' => $room->id_room,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai
            ]);
            
            $isAvailable = $room->isAvailable(
                $request->tanggal_mulai,
                $request->tanggal_selesai
            );

            \Log::info('Room availability result', ['is_available' => $isAvailable]);

            if (! $isAvailable) {
                \Log::warning('Room not available for selected dates');
                
                // Ambil booking yang bentrok untuk pesan error yang lebih detail
                $conflictBooking = $room->bookings()
                    ->whereIn('status', ['diterima', 'proses'])
                    ->where(function ($query) use ($request) {
                        $query->where('tanggal_mulai', '<', $request->tanggal_selesai)
                              ->where('tanggal_selesai', '>', $request->tanggal_mulai);
                    })
                    ->first();
                
                $message = 'Ruangan tidak tersedia untuk waktu yang dipilih';
                if ($conflictBooking) {
                    $conflictStart = \Carbon\Carbon::parse($conflictBooking->tanggal_mulai)->format('d/m/Y H:i');
                    $conflictEnd = \Carbon\Carbon::parse($conflictBooking->tanggal_selesai)->format('d/m/Y H:i');
                    $statusText = $conflictBooking->status === 'diterima' ? 'sudah diterima' : 'sedang diproses';
                    
                    $message = "Ruangan tidak tersedia. Ada booking yang {$statusText} dari {$conflictStart} sampai {$conflictEnd}. Silakan pilih waktu lain.";
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 400);
            }

            // Membuat peminjaman - assign ke petugas pertama yang tersedia
            // Untuk distribusi yang lebih baik, bisa gunakan round-robin
            $petugas = \App\Models\Petugas::first();

            // Jika belum ada record petugas, coba buat otomatis dari user dengan role=2 (petugas)
            if (! $petugas) {
                $petugasUser = \App\Models\User::where('role', 2)->first();
                if ($petugasUser) {
                    \Log::info('No Petugas found. Creating Petugas from existing user with role=2', [
                        'user_id' => $petugasUser->id,
                        'username' => $petugasUser->username,
                    ]);
                    $petugas = \App\Models\Petugas::create([
                        'nama_petugas' => $petugasUser->username,
                        'id_user' => $petugasUser->id,
                    ]);
                }
            }

            if (! $petugas) {
                // Coba fallback kedua: gunakan admin (role=1) sebagai petugas default
                $adminUser = \App\Models\User::where('role', 1)->first();
                if ($adminUser) {
                    \Log::info('No Petugas with role=2. Creating Petugas from admin user as fallback', [
                        'user_id' => $adminUser->id,
                        'username' => $adminUser->username,
                    ]);
                    $petugas = \App\Models\Petugas::create([
                        'nama_petugas' => $adminUser->username,
                        'id_user' => $adminUser->id,
                    ]);
                }
            }

            if (! $petugas) {
                // Kegagalan domain (bukan error server internal) → gunakan 422 agar bisa ditangani klien
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada petugas yang tersedia. Silakan hubungi admin.',
                ], 422);
            }

            $booking = Booking::create([
                'id_user' => $user->id,
                'id_petugas' => $petugas->id_petugas, // Auto-assign ke petugas
                'id_room' => $request->id_room,
                'tipe_booking' => $request->tipe_booking,
                // Gratis
                'harga' => 0,
                'durasi' => $request->durasi,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'keterangan' => $request->keterangan,
                'status' => 'proses', // Status default sesuai migrasi
            ]);

            \Log::info('Booking created successfully', [
                'booking_id' => $booking->id_booking,
                'assigned_petugas' => $petugas->id_petugas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil dibuat',
                'data' => $booking->load('room'),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Booking creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat peminjaman: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan peminjaman tertentu.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::with('room')
            ->where('id_booking', $id)
            ->where('id_user', $user->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail peminjaman berhasil diambil',
            'data' => $booking,
        ]);
    }

    /**
     * Memperbarui peminjaman tertentu.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::where('id_booking', $id)
            ->where('id_user', $user->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        // Hanya mengizinkan pembaruan jika status peminjaman adalah 'proses'
        if ($booking->status !== 'proses') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat memperbarui peminjaman dengan status: '.$booking->status,
            ], 400);
        }

        $request->validate([
            'id_room' => 'sometimes|required|exists:room,id_room',
            'tanggal_mulai' => 'sometimes|required|date|after_or_equal:today',
            'tanggal_selesai' => 'sometimes|required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'sometimes|required|string',
        ]);

        // Memeriksa jika tanggal atau ruangan berubah
        $roomChanged = $request->has('id_room') && $request->id_room != $booking->id_room;
        $datesChanged = $request->has('tanggal_mulai') || $request->has('tanggal_selesai');

        if ($roomChanged || $datesChanged) {
            $roomId = $request->id_room ?? $booking->id_room;
            $startDate = $request->tanggal_mulai ?? $booking->tanggal_mulai;
            $endDate = $request->tanggal_selesai ?? $booking->tanggal_selesai;

            $room = Room::find($roomId);

            // Memeriksa ketersediaan ruangan tidak termasuk peminjaman saat ini
            $isAvailable = $room->isAvailable($startDate, $endDate);

            if (! $isAvailable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak tersedia untuk tanggal yang dipilih',
                ], 400);
            }
        }

        // Memperbarui peminjaman
        $booking->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diperbarui',
            'data' => $booking->fresh()->load('room'),
        ]);
    }

    /**
     * Menghapus peminjaman tertentu.
     *
     * @param  int  $id
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::where('id_booking', $id)
            ->where('id_user', $user->id)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        // Hanya mengizinkan pembatalan jika status peminjaman adalah 'proses'
        if ($booking->status !== 'proses') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan peminjaman dengan status: '.$booking->status,
            ], 400);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dibatalkan',
        ]);
    }
}
