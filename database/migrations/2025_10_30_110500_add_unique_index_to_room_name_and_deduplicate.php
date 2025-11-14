<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan duplikasi berdasarkan nama_room sebelum menambah unique index
        $duplicates = DB::table('room')
            ->select('nama_room', DB::raw('COUNT(*) as cnt'))
            ->groupBy('nama_room')
            ->having('cnt', '>', 1)
            ->pluck('nama_room');

        foreach ($duplicates as $name) {
            $rows = DB::table('room')->where('nama_room', $name)->orderBy('id_room')->get();
            if ($rows->count() <= 1) continue;

            $keep = $rows->first();
            $keepId = $keep->id_room;

            $others = $rows->slice(1);
            foreach ($others as $dup) {
                $dupId = $dup->id_room;
                // Re-map foreign keys ke id yang dipertahankan
                DB::table('booking')->where('id_room', $dupId)->update(['id_room' => $keepId]);
                DB::table('jadwal_reguler')->where('id_room', $dupId)->update(['id_room' => $keepId]);
                // Hapus duplikasi
                DB::table('room')->where('id_room', $dupId)->delete();
            }
        }

        if (Schema::hasTable('room')) {
            Schema::table('room', function (Blueprint $table) {
                // Gunakan metode portable agar jalan di SQLite & MySQL
                // Cek dulu apakah index sudah ada (beberapa engine tidak punya API langsung)
                $connection = DB::connection()->getDriverName();
                $hasIndex = false;
                if ($connection === 'mysql') {
                    $indexes = DB::select('SHOW INDEX FROM room');
                    foreach ($indexes as $idx) {
                        if (($idx->Key_name ?? '') === 'room_nama_unique') { $hasIndex = true; break; }
                    }
                } elseif ($connection === 'sqlite') {
                    $indexes = DB::select("PRAGMA index_list('room')");
                    foreach ($indexes as $idx) {
                        if (($idx->name ?? '') === 'room_nama_unique') { $hasIndex = true; break; }
                    }
                }
                if (!$hasIndex) {
                    $table->unique('nama_room', 'room_nama_unique');
                }
            });
        }
    }

    public function down(): void
    {
        // Hapus unique index jika ada
        if (Schema::hasTable('room')) {
            try {
                Schema::table('room', function (Blueprint $table) {
                    $table->dropUnique('room_nama_unique');
                });
            } catch (\Throwable $e) {
                // Fallback untuk SQLite/MySQL jika cara standar gagal
                try { DB::statement('DROP INDEX room_nama_unique'); } catch (\Throwable $e2) { /* ignore */ }
            }
        }
    }
};
