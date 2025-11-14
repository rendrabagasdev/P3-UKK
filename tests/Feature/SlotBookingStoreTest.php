<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Petugas;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotBookingStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function user(): User
    {
        return User::factory()->create(['role' => 3]);
    }

    protected function petugas(): Petugas
    {
        $u = User::factory()->create(['role' => 2]);
        return Petugas::create([
            'nama_petugas' => 'Petugas',
            'id_user' => $u->id_user,
        ]);
    }

    protected function room(): Room
    {
        return Room::create([
            'nama_room' => 'Room Test',
            'lokasi' => 'Lantai 1',
            'deskripsi' => 'Desc',
            'kapasitas' => 5,
            'harga_pagi' => 60000,
            'harga_siang' => 80000,
            'harga_malam' => 100000,
        ]);
    }

    public function test_user_can_create_multi_hour_booking(): void
    {
        $user = $this->user();
        $this->petugas();
        $room = $this->room();

        $resp = $this->actingAs($user)->post(route('user.slot-booking.store'), [
            'id_room' => $room->id_room,
            'tanggal' => '2025-11-11',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'keterangan' => 'Tim A',
        ]);
        $booking = Booking::latest('id_booking')->first();
        $resp->assertRedirect(route('user.slot-booking.confirm', $booking->id_booking));
        $this->assertDatabaseHas('booking', [
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-11 09:00:00',
            'tanggal_selesai' => '2025-11-11 12:00:00',
            'status' => 'proses',
        ]);
    }

    public function test_overlap_booking_is_rejected(): void
    {
        $user = $this->user();
        $this->petugas();
        $room = $this->room();

        // Existing booking 10-12
        $this->actingAs($user)->post(route('user.slot-booking.store'), [
            'id_room' => $room->id_room,
            'tanggal' => '2025-11-11',
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'keterangan' => 'Tim A',
        ]);

        // Attempt overlapping 11-13
        $resp = $this->actingAs($user)->post(route('user.slot-booking.store'), [
            'id_room' => $room->id_room,
            'tanggal' => '2025-11-11',
            'jam_mulai' => '11:00',
            'jam_selesai' => '13:00',
            'keterangan' => 'Tim B',
        ]);

        $resp->assertSessionHas('error');
        $this->assertEquals(1, Booking::count());
    }
}
