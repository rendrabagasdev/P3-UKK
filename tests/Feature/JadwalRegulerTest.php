<?php

namespace Tests\Feature;

use App\Models\JadwalReguler;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalRegulerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run base migrations
        $this->artisan('migrate');
    }

    protected function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 1,
            'nama' => 'Admin Test'
        ]);
    }

    protected function createRoom(): Room
    {
        return Room::create([
            'nama_room' => 'Ruang A',
            'lokasi' => 'Gedung 1',
            'deskripsi' => 'Untuk test',
            'kapasitas' => 30,
            'harga_pagi' => 100000,
            'harga_siang' => 120000,
            'harga_malam' => 150000,
        ]);
    }

    public function test_admin_can_create_regular_schedule(): void
    {
        $admin = $this->makeAdmin();
        $room = $this->createRoom();

        $resp = $this->actingAs($admin)->post(route('jadwal-reguler.store'), [
            'nama_reguler' => 'Blok Seminar',
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-10',
            'tanggal_selesai' => '2025-11-12',
            'keterangan' => 'Dipakai untuk seminar internal'
        ]);

        $resp->assertRedirect(route('jadwal-reguler.index'));
        $this->assertDatabaseHas('jadwal_reguler', [
            'nama_reguler' => 'Blok Seminar',
            'id_room' => $room->id_room,
        ]);
    }

    public function test_overlap_regular_schedule_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        $room = $this->createRoom();

        // Existing jadwal reguler
        JadwalReguler::create([
            'nama_reguler' => 'Blok 1',
            'id_room' => $room->id_room,
            'id_user' => $admin->id_user,
            'tanggal_mulai' => '2025-11-10',
            'tanggal_selesai' => '2025-11-12',
            'keterangan' => '',
        ]);

        $resp = $this->actingAs($admin)->post(route('jadwal-reguler.store'), [
            'nama_reguler' => 'Blok Tabrakan',
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-11', // Overlaps existing
            'tanggal_selesai' => '2025-11-13',
            'keterangan' => 'Test overlap'
        ]);

        $resp->assertSessionHasErrors();
        $this->assertDatabaseMissing('jadwal_reguler', [
            'nama_reguler' => 'Blok Tabrakan'
        ]);
    }
}
