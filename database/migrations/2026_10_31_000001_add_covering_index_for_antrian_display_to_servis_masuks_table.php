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
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->index(
                ['cabang_id', 'is_real', 'kategori_perangkat', 'status'],
                'idx_servis_antrian_covering'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->dropIndex('idx_servis_antrian_covering');
        });
    }
};

