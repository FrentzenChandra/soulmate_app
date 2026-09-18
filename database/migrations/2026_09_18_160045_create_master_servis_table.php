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
        Schema::create('master_servis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_item', 50)->nullable()->unique();
            $table->string('nama_item', 200);
            $table->enum('jenis', ['jasa', 'sparepart'])->default('jasa');
            $table->enum('kategori_perangkat', ['semua', 'laptop', 'printer', 'komputer', 'lainnya'])->default('semua');
            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('garansi_default_hari')->default(30);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_servis');
    }
};
