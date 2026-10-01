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
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('cabang_id')->constrained('users')->nullOnDelete();
            $table->foreignId('pelunas_user_id')->nullable()->after('tanggal_lunas')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['pelunas_user_id']);
            $table->dropColumn(['user_id', 'pelunas_user_id']);
        });
    }
};
