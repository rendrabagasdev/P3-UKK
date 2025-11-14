<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Kunci utama untuk model.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'nama',
        'email',
        'password',
        'role',
        'no_telepon',
        'alamat',
    ];

    /**
     * Atribut yang harus disembunyikan untuk serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Override untuk login dengan username atau email
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->orWhere('email', $username)->first();
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username'; // Default ke username untuk user biasa
    }

    /**
     * Mendapatkan atribut yang harus dikonversi.
     *
     * @return array<string, string>
     */
    // Catatan: hashing password dilakukan eksplisit via Hash::make di controller/seeder.
    // Jangan gunakan cast 'hashed' agar tidak terjadi double-hash.
    protected function casts(): array
    {
        return [];
    }

    /**
     * Mendapatkan petugas yang terkait dengan pengguna.
     */
    public function petugas()
    {
        return $this->hasOne(Petugas::class, 'id_user');
    }

    /**
     * Mendapatkan peminjaman untuk pengguna.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'id_user');
    }

    /**
     * Mendapatkan jadwal reguler untuk pengguna.
     */
    public function jadwalRegulers()
    {
        return $this->hasMany(JadwalReguler::class, 'id_user');
    }
}
