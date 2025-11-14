<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalReguler extends Model
{
    use HasFactory;

    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'jadwal_reguler';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_reguler';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_reguler',
        'id_room',
        'id_user',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
    ];

    /**
     * Atribut yang harus dikonversi.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Mendapatkan ruangan yang memiliki jadwal reguler.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    /**
     * Mendapatkan pengguna yang memiliki jadwal reguler.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
