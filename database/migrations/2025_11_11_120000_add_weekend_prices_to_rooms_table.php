<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room', function (Blueprint $table) {
            $table->decimal('harga_pagi_weekend', 10, 2)->nullable()->after('harga_malam');
            $table->decimal('harga_siang_weekend', 10, 2)->nullable()->after('harga_pagi_weekend');
            $table->decimal('harga_malam_weekend', 10, 2)->nullable()->after('harga_siang_weekend');
        });

        // Backfill weekend prices = weekday prices by default to keep behavior unchanged.
        DB::table('room')->update([
            'harga_pagi_weekend' => DB::raw('COALESCE(harga_pagi_weekend, harga_pagi)'),
            'harga_siang_weekend' => DB::raw('COALESCE(harga_siang_weekend, harga_siang)'),
            'harga_malam_weekend' => DB::raw('COALESCE(harga_malam_weekend, harga_malam)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('room', function (Blueprint $table) {
            $table->dropColumn(['harga_pagi_weekend', 'harga_siang_weekend', 'harga_malam_weekend']);
        });
    }
};
