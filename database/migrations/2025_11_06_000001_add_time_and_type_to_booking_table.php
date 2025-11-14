<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeAndTypeToBookingTable extends Migration
{
    /**
     * Menjalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking', function (Blueprint $table) {
            // Ubah tanggal_mulai dan tanggal_selesai menjadi datetime
            $table->dateTime('tanggal_mulai')->change();
            $table->dateTime('tanggal_selesai')->change();
            
            // Tambah kolom tipe booking (hourly/daily) dan harga
            $table->enum('tipe_booking', ['hourly', 'daily'])->default('hourly')->after('id_room');
            $table->decimal('harga', 10, 2)->default(0)->after('tipe_booking');
            $table->integer('durasi')->default(1)->after('harga')->comment('Durasi dalam jam untuk hourly, hari untuk daily');
        });
    }

    /**
     * Membalikkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropColumn(['tipe_booking', 'harga', 'durasi']);
            $table->date('tanggal_mulai')->change();
            $table->date('tanggal_selesai')->change();
        });
    }
}
