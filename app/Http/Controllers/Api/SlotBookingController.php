<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SlotBookingController extends Controller
{
    public function getRooms(): JsonResponse
    {
        $rooms = Room::all()->map(function ($room) {
            return [
                'id_room' => $room->id_room,
                'nama_room' => $room->nama_room,
                'lokasi' => $room->lokasi,
                'kapasitas' => $room->kapasitas,
                'deskripsi' => $room->deskripsi,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar ruangan berhasil diambil',
            'data' => $rooms,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'id_room' => 'required|exists:room,id_room',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required|string|max:500',
        ], [
            'id_room.required' => 'Ruangan harus dipilih',
            'tanggal.required' => 'Tanggal harus diisi',
            'jam_mulai.required' => 'Jam mulai harus diisi',
            'jam_selesai.required' => 'Jam selesai harus diisi',
            'keterangan.required' => 'Keterangan harus diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $room = Room::findOrFail($request->id_room);
        
        $tanggalMulai = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
        
        $durasi = $tanggalMulai->diffInHours($tanggalSelesai);
        
        if ($durasi <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jam selesai harus lebih dari jam mulai!',
            ], 400);
        }

        if (!$room->isAvailable($tanggalMulai, $tanggalSelesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan sudah dibooking untuk waktu tersebut!',
            ], 400);
        }

    // Gratis: tidak ada biaya peminjaman
    $harga = 0;

        $petugas = \App\Models\Petugas::first();
        if (!$petugas) {
            $petugasUser = \App\Models\User::where('role', 2)->orWhere('role', 1)->first();
            if ($petugasUser) {
                $petugas = \App\Models\Petugas::create([
                    'nama_petugas' => $petugasUser->username,
                    'id_user' => $petugasUser->id_user,
                ]);
            }
        }

        $booking = Booking::create([
            'id_user' => optional(Auth::user())->id_user,
            'id_room' => $request->id_room,
            'id_petugas' => $petugas->id_petugas,
            'tipe_booking' => 'hourly',
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'harga' => $harga,
            'durasi' => $durasi,
            'keterangan' => $request->keterangan,
            'status' => 'proses',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil! Menunggu persetujuan.',
            'data' => $booking->load('room'),
        ], 201);
    }

    public function myBookings(): JsonResponse
    {
        $bookings = Booking::where('id_user', optional(Auth::user())->id_user)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id_booking' => $booking->id_booking,
                    'room_name' => $booking->room->nama_room,
                    'tanggal' => Carbon::parse($booking->tanggal_mulai)->format('d M Y'),
                    'jam_mulai' => Carbon::parse($booking->tanggal_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($booking->tanggal_selesai)->format('H:i'),
                    'durasi' => $booking->durasi . ' jam',
                    'status' => $booking->status,
                    'keterangan' => $booking->keterangan,
                    'created_at' => $booking->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    private function calculatePrice($room, $start, $end)
    {
        // Dispensable for now; keep method for backward compatibility. Always free.
        return 0;
    }
}
