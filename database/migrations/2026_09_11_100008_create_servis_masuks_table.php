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
        Schema::create('servis_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('no_servis', 50)->unique();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            // Kolom untuk display Smart TV Antrian & detail unit
            $table->string('kategori_perangkat', 50)->default('laptop')->comment('laptop, printer, komputer, lainnya');
            $table->string('tipe_unit', 150)->nullable()->comment('Contoh: Asus ROG G14, Epson L3110');
            $table->string('serial_number', 100)->nullable();
            $table->text('kelengkapan')->nullable()->comment('Contoh: Unit + Adaptor + Tas');
            
            $table->text('keluhan');
            $table->text('kondisi_barang')->nullable();
            $table->string('status', 30)->default('pengecekan')->comment('pengecekan, pengerjaan, selesai, sudah_dibayar, batal');
            
            $table->dateTime('tanggal_masuk');
            $table->dateTime('tanggal_selesai')->nullable();
            $table->dateTime('tanggal_ambil')->nullable();
            
            $table->decimal('estimasi_biaya', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'status']);
            $table->index(['cabang_id', 'kategori_perangkat', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servis_masuks');
    }
};
