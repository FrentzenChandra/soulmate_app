<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutup_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->index();
            $table->dateTime('waktu_tutup');
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->decimal('total_penjualan_cash', 15, 2)->default(0);
            $table->decimal('total_servis_cash', 15, 2)->default(0);
            $table->decimal('total_kas_keluar_cash', 15, 2)->default(0);
            $table->decimal('kas_sistem', 15, 2)->default(0);
            $table->decimal('kas_fisik', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->string('status_selisih')->default('klop'); // klop, minus, plus
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutup_kas');
    }
};
