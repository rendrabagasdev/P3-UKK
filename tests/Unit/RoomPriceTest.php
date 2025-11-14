<?php

namespace Tests\Unit;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomPriceTest extends TestCase
{
    use RefreshDatabase;

    protected function room(): Room
    {
        return Room::create([
            'nama_room' => 'R1',
            'lokasi' => 'L1',
            'deskripsi' => 'T',
            'kapasitas' => 1,
            'harga_pagi' => 60000,
            'harga_siang' => 80000,
            'harga_malam' => 100000,
        ]);
    }

    public function test_pagi_only_pricing(): void
    {
        $room = $this->room();
        $price = $room->priceForRange('2025-11-11 06:00:00', '2025-11-11 09:00:00');
        $this->assertSame(60000.0 * 3, $price);
    }

    public function test_cross_noon_boundary(): void
    {
        $room = $this->room();
        // 11:00-13:00 => 1 hour pagi + 1 hour siang
        $price = $room->priceForRange('2025-11-11 11:00:00', '2025-11-11 13:00:00');
        $this->assertSame(60000.0 + 80000.0, $price);
    }

    public function test_evening_segment(): void
    {
        $room = $this->room();
        $price = $room->priceForRange('2025-11-11 19:00:00', '2025-11-11 22:00:00');
        $this->assertSame(100000.0 * 3, $price);
    }

    public function test_weekend_override_pricing(): void
    {
        $room = $this->room();

        // Set explicit weekend overrides different from weekday to verify branch
        $room->update([
            'harga_pagi_weekend' => 90000,
            'harga_siang_weekend' => 120000,
            'harga_malam_weekend' => 150000,
        ]);

        // Find a Friday date to ensure weekend logic triggers (Fri/Sat/Sun)
        $friday = Carbon::parse('2025-11-01')->next(Carbon::FRIDAY)->toDateString();

        // 06:00-09:00 on Friday => 3 hours, should use weekend pagi rate
        $start = "$friday 06:00:00";
        $end = "$friday 09:00:00";
        $price = $room->priceForRange($start, $end);

        $this->assertSame(90000.0 * 3, $price);
    }
}
