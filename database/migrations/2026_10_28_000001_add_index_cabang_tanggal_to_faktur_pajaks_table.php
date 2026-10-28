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
        Schema::table('faktur_pajaks', function (Blueprint $table) {
            $table->index(['cabang_id', 'tanggal'], 'idx_faktur_pajaks_cabang_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faktur_pajaks', function (Blueprint $table) {
            $table->dropIndex('idx_faktur_pajaks_cabang_tanggal');
        });
    }
};
