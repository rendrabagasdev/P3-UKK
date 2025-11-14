<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'room';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_room';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_room',
        'lokasi',
        'deskripsi',
        'kapasitas',
    ];

    /**
     * Mendapatkan peminjaman untuk ruangan.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'id_room');
    }

    /**
     * Mendapatkan jadwal reguler untuk ruangan.
     */
    public function jadwalRegulers()
    {
        return $this->hasMany(JadwalReguler::class, 'id_room');
    }

    /**
     * Memeriksa apakah ruangan tersedia untuk rentang tanggal tertentu.
     *
     * @param  string  $tanggalMulai
     * @param  string  $tanggalSelesai
     */
    public function isAvailable($tanggalMulai, $tanggalSelesai, $excludeBookingId = null): bool
    {
        $startDateTime = Carbon::parse($tanggalMulai);
        $endDateTime = Carbon::parse($tanggalSelesai);

        // Memeriksa peminjaman yang tumpang tindih dengan status 'diterima' atau 'proses'
        // Karena booking yang sedang diproses juga perlu dicek agar tidak double booking
        // Logika overlap: existing.start < new.end AND existing.end > new.start
        $overlappingBookings = $this->bookings()
            ->whereIn('status', ['diterima', 'proses']) // Cek booking yang diterima atau masih proses
            ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
                $q->where('id_booking', '!=', $excludeBookingId);
            })
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                // Cek overlap waktu dengan detail sampai jam/menit
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    // Booking existing dimulai sebelum booking baru selesai
                    // DAN booking existing selesai setelah booking baru dimulai
                    $q->where('tanggal_mulai', '<', $endDateTime)
                      ->where('tanggal_selesai', '>', $startDateTime);
                });
            })
            ->count();

        if ($overlappingBookings > 0) {
            return false;
        }

        // Cek tabrakan dengan Jadwal Reguler (diasumsikan full-day blok)
        // Aturan overlap tanggal: existing.start_date <= new.end_date AND existing.end_date >= new.start_date
        $startDate = $startDateTime->toDateString();
        $endDate = $endDateTime->toDateString();

        $overlappingRegular = $this->jadwalRegulers()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('tanggal_mulai', '<=', $endDate)
                  ->where('tanggal_selesai', '>=', $startDate);
            })
            ->count();

        return $overlappingRegular === 0;
    }

    /**
     * Perhitungan harga dinonaktifkan (sistem non-berbayar).
     */
    public function priceForRange($start, $end): float
    {
        // Sistem telah dialihkan menjadi gratis; kembalikan 0 selalu.
        return 0.0;
    }
}
