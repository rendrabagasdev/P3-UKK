<?php

namespace App\Http\Controllers\Web;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBookingController extends BaseController
{
    /**
     * Menampilkan daftar peminjaman ruangan pengguna
     */
    public function index()
    {
        if ($redirect = $this->authorizeRoles([3])) {
            return $redirect;
        }
        
        $rooms = Room::orderBy('nama_room')->get();
        return view('user.bookings.index', compact('rooms'));
    }
    
    // Halaman riwayat dihapus sesuai permintaan; gunakan dashboard atau notifikasi di halaman form
    // Kini diaktifkan kembali: pengguna dapat melihat semua booking mereka & statusnya

    /**
     * Menampilkan riwayat semua booking user dengan filter status
     */
    public function history(Request $request)
    {
        if ($redirect = $this->authorizeRoles([3])) {
            return $redirect;
        }

        $user = Auth::user();
        $status = $request->query('status'); // proses | diterima | ditolak
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        $query = Booking::with('room')
            ->where('id_user', $user->id)
            ->orderByDesc('tanggal_mulai');
        if ($status && in_array($status, ['proses','diterima','ditolak'])) {
            $query->where('status', $status);
        }
        if ($dari) {
            $query->whereDate('tanggal_mulai', '>=', $dari);
        }
        if ($sampai) {
            $query->whereDate('tanggal_selesai', '<=', $sampai);
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('user.bookings.history', compact('bookings','status'));
    }
    
    /**
     * Menyimpan peminjaman ruangan baru
     */
    public function store(Request $request)
    {        
        if ($redirect = $this->authorizeRoles([3])) {
            return $redirect;
        }
        
        $user = Auth::user();
        
        $request->validate([
            'id_room' => 'required|exists:room,id_room',
            'tipe_booking' => 'required|in:hourly,daily',
            'keterangan' => 'required|string',
        ]);

        $room = Room::find($request->id_room);
        if (!$room) {
            return back()->with('error', 'Ruangan tidak ditemukan.');
        }

        // Proses berdasarkan tipe booking
        if ($request->tipe_booking === 'hourly') {
            $request->validate([
                'tanggal' => 'required|date|after_or_equal:today',
                'jam_mulai' => 'required',
                'durasi_jam' => 'required|integer|min:1|max:12',
            ]);

            $tanggalMulai = $request->tanggal . ' ' . $request->jam_mulai;
            $tanggalSelesai = date('Y-m-d H:i:s', strtotime($tanggalMulai . ' + ' . $request->durasi_jam . ' hours'));
            $durasi = $request->durasi_jam;
            // Gratis
            $harga = 0;
        } else {
            $request->validate([
                'tanggal_mulai_daily' => 'required|date|after_or_equal:today',
                'tanggal_selesai_daily' => 'required|date|after_or_equal:tanggal_mulai_daily',
            ]);

            $tanggalMulai = $request->tanggal_mulai_daily . ' 00:00:00';
            $tanggalSelesai = $request->tanggal_selesai_daily . ' 23:59:59';
            
            $start = new \DateTime($request->tanggal_mulai_daily);
            $end = new \DateTime($request->tanggal_selesai_daily);
            $durasi = $end->diff($start)->days + 1;
            // Gratis
            $harga = 0;
        }

        // Cek ketersediaan
    $isAvailable = $room->isAvailable($tanggalMulai, $tanggalSelesai);
        if (!$isAvailable) {
            // Ambil booking yang bentrok untuk ditampilkan ke user
            $conflictBooking = $room->bookings()
                ->whereIn('status', ['diterima', 'proses'])
                ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                    $query->where('tanggal_mulai', '<', $tanggalSelesai)
                          ->where('tanggal_selesai', '>', $tanggalMulai);
                })
                ->first();
            
            if ($conflictBooking) {
                $conflictStart = \Carbon\Carbon::parse($conflictBooking->tanggal_mulai)->format('d/m/Y H:i');
                $conflictEnd = \Carbon\Carbon::parse($conflictBooking->tanggal_selesai)->format('d/m/Y H:i');
                $statusText = $conflictBooking->status === 'diterima' ? 'sudah diterima' : 'sedang diproses';
                
                return back()->with('error', "Ruangan tidak tersedia pada waktu tersebut. Ada booking yang {$statusText} dari {$conflictStart} sampai {$conflictEnd}. Silakan pilih waktu lain.");
            }
            
            return back()->with('error', 'Ruangan tidak tersedia pada waktu tersebut. Silakan pilih waktu lain.');
        }

        // Auto-assign petugas (sama seperti alur SlotBookingController)
        $petugas = \App\Models\Petugas::first();
        if (!$petugas) {
            $petugasUser = \App\Models\User::where('role', 2)->orWhere('role', 1)->first();
            if ($petugasUser) {
                $petugas = \App\Models\Petugas::create([
                    'nama_petugas' => $petugasUser->username,
                    'id_user' => $petugasUser->id,
                ]);
            }
        }

        // Jika masih belum ada petugas sama sekali, cegah error FK dan beri info jelas
        if (!$petugas) {
            return back()->with('error', 'Belum ada petugas/admin terdaftar. Tambahkan petugas atau admin terlebih dahulu.');
        }

        $booking = Booking::create([
            'id_user' => $user->id,
            'id_petugas' => $petugas->id_petugas,
            'id_room' => $request->id_room,
            'tipe_booking' => $request->tipe_booking,
            'harga' => $harga,
            'durasi' => $durasi,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'keterangan' => $request->keterangan,
            'status' => 'proses',
        ]);
        
        // Setelah submit, arahkan user ke menu slot booking (seperti pola Flutter: kembali ke menu)
        return redirect()->route('user.slot-booking.index')
            ->with('success', 'Permintaan peminjaman ruangan berhasil dibuat.');
    }
    
    /**
     * Membatalkan peminjaman ruangan
     */
    public function cancel($id)
    {
        if ($redirect = $this->authorizeRoles([3])) {
            return $redirect;
        }
        
        $user = Auth::user();
        $booking = Booking::where('id_booking', $id)->where('id_user', $user->id)->first();
        
        if (!$booking) {
            return redirect()->route('user.slot-booking.index')->with('error', 'Peminjaman tidak ditemukan.');
        }
        
        // Hanya bisa membatalkan peminjaman yang masih proses
        if ($booking->status != 'proses') {
            return redirect()->route('user.slot-booking.index')->with('error', 'Hanya peminjaman yang statusnya menunggu yang bisa dibatalkan.');
        }
        
        $booking->delete();
        
        return redirect()->route('user.slot-booking.index')->with('success', 'Peminjaman ruangan berhasil dibatalkan.');
    }
}
