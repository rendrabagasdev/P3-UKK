<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\JadwalReguler;
use App\Models\Petugas;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SlotAvailableTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(int $role = 3): User
    {
        return User::factory()->create(['role' => $role]);
    }

    protected function makeRoom(): Room
    {
        return Room::create([
            'nama_room' => 'Room A',
            'lokasi' => 'L1',
            'deskripsi' => 'Test',
            'kapasitas' => 10,
            'harga_pagi' => 60000,
            'harga_siang' => 80000,
            'harga_malam' => 100000,
        ]);
    }

    public function test_returns_blocked_when_jadwal_reguler_covers_date(): void
    {
        $user = $this->makeUser();
        $room = $this->makeRoom();

        JadwalReguler::create([
            'nama_reguler' => 'Libur',
            'id_room' => $room->id_room,
            'id_user' => $user->id_user,
            'tanggal_mulai' => '2025-11-11',
            'tanggal_selesai' => '2025-11-11',
            'keterangan' => 'off',
        ]);

        $resp = $this->actingAs($user)
            ->getJson(route('user.slot-booking.available', [
                'id_room' => $room->id_room,
                'tanggal' => '2025-11-11',
            ]));

        $resp->assertOk()
             ->assertJson([
                 'date' => '2025-11-11',
                 'room_id' => $room->id_room,
                 'blocked' => true,
             ]);
    }

    public function test_lists_occupied_intervals_and_free_when_not_blocked(): void
    {
        $user = $this->makeUser();
        $room = $this->makeRoom();

        // Create petugas and an overlapping booking 09:00-11:00
        $petugasUser = $this->makeUser(2);
        $petugas = Petugas::create([
            'nama_petugas' => 'Tester',
            'id_user' => $petugasUser->id_user,
        ]);
        Booking::create([
            'id_user' => $user->id_user,
            'id_petugas' => $petugas->id_petugas,
            'id_room' => $room->id_room,
            'tipe_booking' => 'hourly',
            'harga' => 0,
            'durasi' => 2,
            'tanggal_mulai' => '2025-11-12 09:00:00',
            'tanggal_selesai' => '2025-11-12 11:00:00',
            'status' => 'proses',
            'keterangan' => '',
        ]);

        $resp = $this->actingAs($user)
            ->getJson(route('user.slot-booking.available', [
                'id_room' => $room->id_room,
                'tanggal' => '2025-11-12',
            ]));

        $resp->assertOk()
            ->assertJson([
                'date' => '2025-11-12',
                'room_id' => $room->id_room,
                'blocked' => false,
            ]);

        $json = $resp->json();
        $this->assertNotEmpty($json['occupied']);
        $this->assertEquals('09:00', $json['occupied'][0]['start']);
        $this->assertEquals('11:00', $json['occupied'][0]['end']);
        $this->assertIsArray($json['free']);
    }
}
