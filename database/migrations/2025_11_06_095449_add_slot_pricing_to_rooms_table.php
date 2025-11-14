<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room', function (Blueprint $table) {
            // Hapus kolom lama jika ada
            if (Schema::hasColumn('room', 'harga_per_jam')) {
                $table->dropColumn(['harga_per_jam', 'harga_per_hari']);
            }
            
            // Tambah harga per slot waktu
            $table->decimal('harga_pagi', 10, 2)->default(60000)->after('kapasitas'); // 06:00-12:00
            $table->decimal('harga_siang', 10, 2)->default(80000)->after('harga_pagi'); // 12:00-18:00
            $table->decimal('harga_malam', 10, 2)->default(100000)->after('harga_siang'); // 18:00-24:00
        });
    }

    public function down(): void
    {
        Schema::table('room', function (Blueprint $table) {
            $table->dropColumn(['harga_pagi', 'harga_siang', 'harga_malam']);
            $table->decimal('harga_per_jam', 10, 2)->default(50000);
            $table->decimal('harga_per_hari', 10, 2)->default(300000);
        });
    }
};
