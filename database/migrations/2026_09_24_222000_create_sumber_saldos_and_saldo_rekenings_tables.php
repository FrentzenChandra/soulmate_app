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
        Schema::create('sumber_saldos', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('saldo_rekenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sumber_saldo_id')->constrained('sumber_saldos')->cascadeOnDelete();
            $table->string('nama_bank', 100);
            $table->string('atas_nama', 150)->nullable();
            $table->string('nomor_rekening', 100)->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_rekenings');
        Schema::dropIfExists('sumber_saldos');
    }
};
