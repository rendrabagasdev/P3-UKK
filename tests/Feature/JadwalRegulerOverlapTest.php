<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\JadwalReguler;
use App\Models\Room;
use App\Models\User;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JadwalRegulerOverlapTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create([
            'role' => 1,
        ]);
    }

    private function makeRoom(): Room
    {
        return Room::create([
            'nama_room' => 'Ruang Uji',
            'lokasi' => 'Lantai 1',
            'deskripsi' => 'Untuk pengujian',
            'kapasitas' => 10,
        ]);
    }

    public function test_store_blocks_overlap_with_existing_regular_schedule(): void
    {
        $this->actingAs($this->adminUser());
        $room = $this->makeRoom();

        // Existing reguler: 2025-11-10 .. 2025-11-12
        JadwalReguler::create([
            'nama_reguler' => 'Existing',
            'id_room' => $room->id_room,
            'id_user' => auth()->user()->id_user,
            'tanggal_mulai' => '2025-11-10',
            'tanggal_selesai' => '2025-11-12',
            'keterangan' => '',
        ]);

        // New reguler overlaps: 2025-11-11 .. 2025-11-13
        $resp = $this->post(route('jadwal-reguler.store'), [
            'nama_reguler' => 'Overlap',
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-11',
            'tanggal_selesai' => '2025-11-13',
            'keterangan' => '',
        ]);

        $resp->assertSessionHasErrors();
        $this->assertDatabaseCount('jadwal_reguler', 1);
    }

    public function test_store_blocks_overlap_with_existing_booking(): void
    {
        $this->actingAs($this->adminUser());
        $room = $this->makeRoom();

        // Existing booking: proses, 2025-11-10 .. 2025-11-12
        // Buat user petugas minimal agar field id_petugas tidak null jika wajib
        // Buat user petugas & entri petugas (ambil id_petugas yang benar)
        $petugasUser = User::factory()->create(['role' => 2]);
        $petugas = Petugas::create([
            'nama_petugas' => 'Tester',
            'id_user' => $petugasUser->id_user,
        ]);
        Booking::create([
            'id_user' => auth()->user()->id_user,
            'id_petugas' => $petugas->id_petugas,
            'id_room' => $room->id_room,
            'tipe_booking' => 'daily',
            'harga' => 0,
            'durasi' => 0,
            'tanggal_mulai' => '2025-11-10 00:00:00',
            'tanggal_selesai' => '2025-11-12 23:59:59',
            'status' => 'proses',
            'keterangan' => '',
            'alasan_tolak' => null,
        ]);

        // New reguler overlaps: 2025-11-11 .. 2025-11-11
        $resp = $this->post(route('jadwal-reguler.store'), [
            'nama_reguler' => 'Overlap Booking',
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-11',
            'tanggal_selesai' => '2025-11-11',
            'keterangan' => '',
        ]);

        $resp->assertSessionHasErrors();
        $this->assertDatabaseCount('jadwal_reguler', 0);
    }

    public function test_store_allows_non_overlapping_dates(): void
    {
        $this->actingAs($this->adminUser());
        $room = $this->makeRoom();

        // Existing reguler: 2025-11-10 .. 2025-11-12
        JadwalReguler::create([
            'nama_reguler' => 'Existing',
            'id_room' => $room->id_room,
            'id_user' => auth()->user()->id_user,
            'tanggal_mulai' => '2025-11-10',
            'tanggal_selesai' => '2025-11-12',
            'keterangan' => '',
        ]);

        // New reguler starts after: 2025-11-13 .. 2025-11-14
        $resp = $this->post(route('jadwal-reguler.store'), [
            'nama_reguler' => 'Non Overlap',
            'id_room' => $room->id_room,
            'tanggal_mulai' => '2025-11-13',
            'tanggal_selesai' => '2025-11-14',
            'keterangan' => '',
        ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect(route('jadwal-reguler.index'));
        $this->assertDatabaseCount('jadwal_reguler', 2);
    }

    public function test_index_filters_work(): void
    {
        $this->actingAs($this->adminUser());
        $roomA = $this->makeRoom();
        $roomB = Room::create([
            'nama_room' => 'Ruang Lain',
            'lokasi' => 'Lantai 2',
            'deskripsi' => 'Lainnya',
            'kapasitas' => 5,
        ]);

        JadwalReguler::create([
            'nama_reguler' => 'Kuliah Pagi',
            'id_room' => $roomA->id_room,
            'id_user' => auth()->user()->id_user,
            'tanggal_mulai' => '2025-11-01',
            'tanggal_selesai' => '2025-11-05',
            'keterangan' => 'pagi',
        ]);

        JadwalReguler::create([
            'nama_reguler' => 'Rapat Sore',
            'id_room' => $roomB->id_room,
            'id_user' => auth()->user()->id_user,
            'tanggal_mulai' => '2025-11-10',
            'tanggal_selesai' => '2025-11-10',
            'keterangan' => 'sore',
        ]);

        // Filter room A + keyword 'Kuliah' + date window overlapping 2025-11-03..2025-11-03
        $resp = $this->get(route('jadwal-reguler.index', [
            'room' => $roomA->id_room,
            'q' => 'Kuliah',
            'from' => '2025-11-03',
            'to' => '2025-11-03',
        ]));

        $resp->assertStatus(200);
        $resp->assertSee('Kuliah Pagi');
        $resp->assertDontSee('Rapat Sore');
    }
}
