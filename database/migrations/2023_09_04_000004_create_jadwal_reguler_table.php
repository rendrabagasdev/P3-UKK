<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJadwalRegulerTable extends Migration
{
    /**
     * Menjalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwal_reguler', function (Blueprint $table) {
            $table->id('id_reguler');
            $table->string('nama_reguler', 50);
            $table->unsignedBigInteger('id_room');
            $table->unsignedBigInteger('id_user');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan');
            $table->foreign('id_room')->references('id_room')->on('room')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Membalikkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jadwal_reguler');
    }
}
