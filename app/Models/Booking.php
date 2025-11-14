<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'booking';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_booking';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user',
        'id_petugas',
        'id_room',
        'tipe_booking',
        'harga',
        'durasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
        'alasan_tolak',
    ];

    /**
     * Atribut yang harus dikonversi.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'harga' => 'decimal:2',
    ];

    /**
     * Mendapatkan pengguna yang memiliki peminjaman.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Mendapatkan petugas yang memiliki peminjaman.
     */
    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas');
    }

    /**
     * Mendapatkan ruangan yang terkait dengan peminjaman.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }
}
