<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->foreignId('pegawai_id')->nullable()->after('cabang_id')->constrained('pegawais')->nullOnDelete();
        });

        // Drop foreign key and user_id column
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('cabang_id')->constrained('users')->nullOnDelete();
            $table->dropForeign(['pegawai_id']);
            $table->dropColumn('pegawai_id');
        });
    }
};
