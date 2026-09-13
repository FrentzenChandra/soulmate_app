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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 50)->unique()->comment('Contoh: INV-PJ-000001 / INV-SV-000001');
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('kasir_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('servis_id')->nullable()->unique()->constrained('servis_masuks')->nullOnDelete()->comment('One-to-one relasi untuk penagihan servis');
            $table->string('tipe_transaksi', 30)->comment('penjualan_barang / pembayaran_servis');

            $table->dateTime('tanggal');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);

            $table->foreignId('ppn_id')->nullable()->constrained('master_ppns')->nullOnDelete();
            $table->decimal('ppn_persen', 5, 2)->default(0);
            $table->decimal('ppn_nominal', 15, 2)->default(0);

            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);

            $table->string('metode_bayar', 30)->nullable()->comment('cash, transfer, qris, debit, dll (opsional/disembunyikan)');
            $table->string('status', 20)->default('lunas')->comment('lunas, batal');
            $table->boolean('is_real')->default(true)->comment('true = penjualan riil potong stok & masuk omset; false = nota simulasi/non-real');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'is_real', 'tipe_transaksi', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
