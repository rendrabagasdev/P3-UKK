<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal_reguler', function (Blueprint $table) {
            // Index gabungan untuk mempercepat filter per ruangan dan rentang tanggal
            $table->index(['id_room', 'tanggal_mulai', 'tanggal_selesai'], 'jr_room_date_range_idx');
        });

        Schema::table('booking', function (Blueprint $table) {
            // Index gabungan untuk mempercepat pengecekan overlap pada booking
            $table->index(['id_room', 'status', 'tanggal_mulai', 'tanggal_selesai'], 'bk_room_status_date_range_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_reguler', function (Blueprint $table) {
            $table->dropIndex('jr_room_date_range_idx');
        });

        Schema::table('booking', function (Blueprint $table) {
            $table->dropIndex('bk_room_status_date_range_idx');
        });
    }
};
