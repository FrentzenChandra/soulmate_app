<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_full_pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_full_id')->constrained('transaksi_fulls')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['transaksi_full_id', 'pegawai_id']);
        });

        // Backfill data historis dari transaksi_fulls.pegawai_id yang sudah ada
        $now = now()->toDateTimeString();
        DB::statement("
            INSERT INTO transaksi_full_pegawai (transaksi_full_id, pegawai_id, created_at, updated_at)
            SELECT id, pegawai_id, COALESCE(created_at, '{$now}'), COALESCE(updated_at, '{$now}')
            FROM transaksi_fulls
            WHERE pegawai_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_full_pegawai');
    }
};

