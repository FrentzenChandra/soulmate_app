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
        // 1. Tabel Master IP WiFi Absensi per Cabang
        Schema::create('wifi_absens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('ip_address', 45);
            $table->boolean('is_aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['cabang_id', 'ip_address']);
            $table->index(['cabang_id', 'is_aktif']);
        });

        // 2. Tabel Rekapitulasi Absensi Staf
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->dateTime('waktu_absen');
            $table->string('ip_address', 45);
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
            $table->index(['cabang_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
        Schema::dropIfExists('wifi_absens');
    }
};

