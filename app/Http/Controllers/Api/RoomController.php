<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Menampilkan daftar ruangan.
     */
    public function index(): JsonResponse
    {
        $rooms = Room::all();

        return response()->json([
            'success' => true,
            'message' => 'Daftar ruangan berhasil diambil',
            'data' => $rooms,
        ]);
    }

    /**
     * Menampilkan ruangan tertentu.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse
    {
        $room = Room::find($id);

        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail ruangan berhasil diambil',
            'data' => $room,
        ]);
    }

    /**
     * Memeriksa ketersediaan ruangan.
     *
     * @param  int  $id
     */
    public function checkAvailability(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $room = Room::find($id);

        if (! $room) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan',
            ], 404);
        }

        // Memeriksa apakah ruangan tersedia dalam rentang tanggal yang diberikan
        $isAvailable = $room->isAvailable(
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        return response()->json([
            'success' => true,
            'message' => 'Ketersediaan ruangan berhasil diperiksa',
            'data' => [
                'room' => $room,
                'is_available' => $isAvailable,
            ],
        ]);
    }
}
