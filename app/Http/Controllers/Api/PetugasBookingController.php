<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetugasBookingController extends Controller
{
    /**
     * Pastikan hanya role petugas (2) yang bisa mengakses.
     */
    protected function ensurePetugas()
    {
        $user = Auth::user();
        if (!$user || (int) $user->role !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya petugas yang diizinkan.',
            ], 403);
        }
        return null;
    }

    /**
     * Helper: Ambil data petugas berdasarkan user yang login
     */
    protected function getCurrentPetugas()
    {
        return Petugas::where('id_user', optional(Auth::user())->id)->first();
    }

    /**
     * Ambil daftar peminjaman yang ditangani petugas (dengan filter status opsional).
     */
    public function index(Request $request)
    {
        if ($resp = $this->ensurePetugas()) {
            return $resp;
        }

        $petugas = $this->getCurrentPetugas();
        if (!$petugas) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada data peminjaman untuk petugas ini',
                'data' => [],
            ]);
        }

        $status = $request->query('status'); // proses|diterima|ditolak|selesai

        $query = Booking::with(['room', 'user'])
            ->where('id_petugas', $petugas->id_petugas)
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman untuk petugas',
            'data' => $bookings,
        ]);
    }

    /**
     * Petugas menyetujui peminjaman.
     */
    public function approve($id)
    {
        if ($resp = $this->ensurePetugas()) {
            return $resp;
        }

        $petugas = $this->getCurrentPetugas();
        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Data petugas tidak ditemukan',
            ], 404);
        }

        $booking = Booking::where('id_booking', $id)
            ->where('id_petugas', $petugas->id_petugas)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        if ($booking->status !== 'proses') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman berstatus proses yang dapat disetujui',
            ], 400);
        }

        $booking->status = 'diterima';
        if (isset($booking->alasan_tolak)) {
            $booking->alasan_tolak = null;
        }
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman disetujui',
            'data' => $booking->fresh()->load(['room', 'user'])
        ]);
    }

    /**
     * Petugas menolak peminjaman.
     */
    public function reject(Request $request, $id)
    {
        if ($resp = $this->ensurePetugas()) {
            return $resp;
        }

        $request->validate([
            'alasan_tolak' => 'required|string',
        ], [
            'alasan_tolak.required' => 'Alasan penolakan wajib diisi',
        ]);

    $petugas = Petugas::where('id_user', optional(Auth::user())->id)->first();
        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Data petugas tidak ditemukan',
            ], 404);
        }

        $booking = Booking::where('id_booking', $id)
            ->where('id_petugas', $petugas->id_petugas)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        if ($booking->status !== 'proses') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman berstatus proses yang dapat ditolak',
            ], 400);
        }

        $booking->status = 'ditolak';
        if (property_exists($booking, 'alasan_tolak')) {
            $booking->alasan_tolak = $request->input('alasan_tolak');
        }
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman ditolak',
            'data' => $booking->fresh()->load(['room', 'user'])
        ]);
    }

    /**
     * Petugas menandai peminjaman selesai.
     */
    public function complete($id)
    {
        if ($resp = $this->ensurePetugas()) {
            return $resp;
        }

        $petugas = $this->getCurrentPetugas();
        if (!$petugas) {
            return response()->json([
                'success' => false,
                'message' => 'Data petugas tidak ditemukan',
            ], 404);
        }

        $booking = Booking::where('id_booking', $id)
            ->where('id_petugas', $petugas->id_petugas)
            ->first();

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak ditemukan',
            ], 404);
        }

        $booking->status = 'selesai';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman ditandai selesai',
            'data' => $booking->fresh()->load(['room', 'user'])
        ]);
    }
}
