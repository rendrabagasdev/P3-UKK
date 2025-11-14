<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('petugas', function (Blueprint $table) {
            if (!Schema::hasColumn('petugas', 'no_hp')) {
                $table->string('no_hp', 20)->nullable()->after('id_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petugas', function (Blueprint $table) {
            if (Schema::hasColumn('petugas', 'no_hp')) {
                $table->dropColumn('no_hp');
            }
        });
    }
};
