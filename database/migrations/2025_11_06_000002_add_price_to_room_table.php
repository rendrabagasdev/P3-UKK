<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToRoomTable extends Migration
{
    /**
     * Menjalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('room', function (Blueprint $table) {
            $table->decimal('harga_per_jam', 10, 2)->default(50000)->after('kapasitas');
            $table->decimal('harga_per_hari', 10, 2)->default(300000)->after('harga_per_jam');
        });
    }

    /**
     * Membalikkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room', function (Blueprint $table) {
            $table->dropColumn(['harga_per_jam', 'harga_per_hari']);
        });
    }
}
