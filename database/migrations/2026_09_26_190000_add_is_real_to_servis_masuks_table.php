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
            $table->boolean('is_real')->default(true)->after('status')->comment('true = servis real potong stok; false = servis simulasi/non-real');
            $table->index(['cabang_id', 'is_real', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->dropIndex(['cabang_id', 'is_real', 'status']);
            $table->dropColumn('is_real');
        });
    }
};
