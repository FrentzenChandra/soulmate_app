<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_fulls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('nama_pelanggan')->default('Pelanggan Umum');
            $table->string('no_hp')->nullable();
            $table->string('no_transaksi')->index();
            $table->string('tipe_transaksi', 30)->default('penjualan'); // 'penjualan' | 'servis'
            $table->dateTime('tanggal')->index();
            $table->foreignId('metode_pembayaran_id')->nullable()->constrained('metode_pembayarans')->nullOnDelete();
            $table->string('metode_pembayaran')->default('Cash');
            $table->foreignId('ppn_id')->nullable()->constrained('master_ppns')->nullOnDelete();
            $table->decimal('ppn_persen', 5, 2)->default(0);
            $table->decimal('ppn_nominal', 15, 2)->default(0);
            $table->decimal('total_modal', 15, 2)->default(0);
            $table->decimal('total_jual', 15, 2)->default(0);
            $table->decimal('total_laba', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->string('status', 30)->default('Lunas'); // 'Lunas', 'BON', 'Belum Lunas', 'Batal'
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('transaksi_full_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_full_id')->constrained('transaksi_fulls')->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('produks')->nullOnDelete();
            $table->string('nama_item');
            $table->integer('qty')->default(1);
            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->decimal('subtotal_modal', 15, 2)->default(0);
            $table->decimal('subtotal_jual', 15, 2)->default(0);
            $table->decimal('laba', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_full_details');
        Schema::dropIfExists('transaksi_fulls');
    }
};
