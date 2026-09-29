<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_pegawais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedInteger('target_bulanan')->default(0);
            $table->json('monthly_targets')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['cabang_id', 'pegawai_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_pegawais');
    }
};
