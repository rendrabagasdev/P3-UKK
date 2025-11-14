<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\JadwalReguler;
use App\Models\Petugas;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_room_and_related_records_are_cascaded(): void
    {
        // Arrange: create admin user and login
        $admin = User::create([
            'username' => 'admin_test',
            'nama' => 'Admin Test',
            'email' => 'admin_test@example.com',
            'password' => Hash::make('secret'),
            'role' => 1,
        ]);
        $this->actingAs($admin);

        // Create a normal user and a petugas user
        $user = User::create([
            'username' => 'user_test',
            'nama' => 'User Test',
            'email' => 'user_test@example.com',
            'password' => Hash::make('secret'),
            'role' => 3,
        ]);

        $petugasUser = User::create([
            'username' => 'petugas_test',
            'nama' => 'Petugas Test',
            'email' => 'petugas_test@example.com',
            'password' => Hash::make('secret'),
            'role' => 2,
        ]);

        $petugas = Petugas::create([
            'nama_petugas' => 'Petugas Satu',
            'id_user' => $petugasUser->id_user,
            'no_hp' => '0800000000',
        ]);

        // Create a room
        $room = Room::create([
            'nama_room' => 'Ruang Uji',
            'lokasi' => 'Gedung A',
            'kapasitas' => 10,
            'deskripsi' => 'Untuk pengujian hapus',
        ]);

        // Add related booking for that room (status proses to ensure overlap-checked rows also cascade)
        Booking::create([
            'id_user' => $user->id_user,
            'id_petugas' => $petugas->id_petugas,
            'id_room' => $room->id_room,
            'tipe_booking' => 'hourly',
            'harga' => 0,
            'durasi' => 2,
            'tanggal_mulai' => Carbon::now()->addDay()->startOfHour(),
            'tanggal_selesai' => Carbon::now()->addDay()->addHours(2)->startOfHour(),
            'status' => 'proses',
            'keterangan' => 'Uji hapus',
        ]);

        // Add related jadwal_reguler for that room
        JadwalReguler::create([
            'nama_reguler' => 'Reguler Uji',
            'id_room' => $room->id_room,
            'id_user' => $user->id_user,
            'tanggal_mulai' => Carbon::now()->toDateString(),
            'tanggal_selesai' => Carbon::now()->addDays(3)->toDateString(),
            'keterangan' => 'Jadwal reguler uji',
        ]);

        // Sanity: ensure data exists
        $this->assertDatabaseHas('room', ['id_room' => $room->id_room]);
        $this->assertDatabaseCount('booking', 1);
        $this->assertDatabaseCount('jadwal_reguler', 1);

    // Act: delete the room via the controller route (provide CSRF token explicitly)
    $this->withSession(['_token' => 'testtoken']);
    $response = $this->delete(route('rooms.destroy', $room->id_room), [], ['X-CSRF-TOKEN' => 'testtoken']);

        // Assert: redirected back to rooms.index with success message
        $response->assertRedirect(route('rooms.index'));
        $response->assertSessionHas('success');

        // Room is deleted
        $this->assertDatabaseMissing('room', ['id_room' => $room->id_room]);
        // Related rows must be deleted by ON DELETE CASCADE
        $this->assertDatabaseCount('booking', 0);
        $this->assertDatabaseCount('jadwal_reguler', 0);
    }
}
