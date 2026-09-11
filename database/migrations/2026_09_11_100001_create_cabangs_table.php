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
        Schema::create('cabangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cabang', 50)->unique();
            $table->string('nama_cabang', 150);
            $table->text('alamat')->nullable();
            $table->string('no_telp', 30)->nullable();
            $table->string('no_wa', 30)->nullable()->comment('Nomor WA khusus cabang untuk pesanan online');
            $table->string('status', 20)->default('aktif')->comment('aktif / nonaktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabangs');
    }
};
