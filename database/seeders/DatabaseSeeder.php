<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\JadwalReguler;
use App\Models\Petugas;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or update core users with correct credentials
        $adminUser = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role' => 1,
            ]
        );
        $adminUser->password = Hash::make('admin123');
        $adminUser->save();

        $petugasUser = User::firstOrCreate(
            ['username' => 'petugas'],
            [
                'nama' => 'Petugas',
                'email' => 'petugas@example.com',
                'password' => Hash::make('petugas123'),
                'role' => 2,
            ]
        );
        $petugasUser->password = Hash::make('petugas123');
        $petugasUser->save();

        $regularUser = User::firstOrCreate(
            ['username' => 'user'],
            [
                'nama' => 'User',
                'email' => 'user@example.com',
                'password' => Hash::make('user123'),
                'role' => 3,
            ]
        );
        $regularUser->password = Hash::make('user123');
        $regularUser->save();

        // Create Petugas record if not exists
        $petugas = Petugas::firstOrCreate(
            ['id_user' => $petugasUser->id_user],
            [
                'nama_petugas' => 'Petugas Satu',
                'no_hp' => '08123456789',
            ]
        );

        // Create Rooms
        $room1 = Room::firstOrCreate([
            'nama_room' => 'Ruang Meeting A',
        ], [
            'nama_room' => 'Ruang Meeting A',
            'lokasi' => 'Lantai 1, Gedung Utama',
            'deskripsi' => 'Ruang meeting dengan kapasitas 10 orang, dilengkapi dengan proyektor dan whiteboard.',
            'kapasitas' => 10,
        ]);

        $room2 = Room::firstOrCreate([
            'nama_room' => 'Ruang Meeting B',
        ], [
            'nama_room' => 'Ruang Meeting B',
            'lokasi' => 'Lantai 2, Gedung Utama',
            'deskripsi' => 'Ruang meeting dengan kapasitas 20 orang, dilengkapi dengan proyektor, whiteboard, dan sistem konferensi video.',
            'kapasitas' => 20,
        ]);

        $room3 = Room::firstOrCreate([
            'nama_room' => 'Ruang Training',
        ], [
            'nama_room' => 'Ruang Training',
            'lokasi' => 'Lantai 3, Gedung Utama',
            'deskripsi' => 'Ruang training dengan kapasitas 30 orang, dilengkapi dengan komputer dan proyektor.',
            'kapasitas' => 30,
        ]);

        // Create Jadwal Reguler
        JadwalReguler::create([
            'nama_reguler' => 'Meeting Mingguan Tim IT',
            'id_room' => $room1->id_room,
            'id_user' => $adminUser->id_user,
            'tanggal_mulai' => '2023-09-10',
            'tanggal_selesai' => '2023-09-10',
            'keterangan' => 'Meeting mingguan tim IT yang diadakan setiap hari Senin.',
        ]);

        // Create Bookings
        Booking::create([
            'id_user' => $regularUser->id_user,
            'id_petugas' => $petugas->id_petugas,
            'id_room' => $room2->id_room,
            'tanggal_mulai' => '2023-09-15',
            'tanggal_selesai' => '2023-09-15',
            'status' => 'diterima',
            'keterangan' => 'Meeting dengan client.',
        ]);
    }
}
