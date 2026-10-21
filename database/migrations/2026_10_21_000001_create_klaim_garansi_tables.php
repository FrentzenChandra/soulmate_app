<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Distributor (id, nama, timestamps, softDeletes)
        Schema::create('master_distributors', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nama');
            $table->index('is_aktif');
        });

        // Seed distributor awal umum
        DB::table('master_distributors')->insert([
            ['nama' => 'ASUS Service Center', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Acer Customer Service Center', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lenovo Service Center', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'HP Authorized Service', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Epson Service Center', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Canon Datascrip Service Center', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'PT Astrindo Senayasa', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'PT Synnex Metrodata Indonesia', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Tabel Klaim Garansi (Zero Status Architecture)
        Schema::create('klaim_garansis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('no_klaim', 50)->unique();

            // Tanggal barang masuk
            $table->date('tanggal_masuk');

            // Data Pelanggan
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_nama', 150);
            $table->string('customer_kontak', 50)->nullable();

            // Barang (Varchar nama produk snapshot, tanpa mutasi stok)
            $table->string('barang', 255);
            $table->text('keluhan');

            // Distributor
            $table->foreignId('master_distributor_id')->nullable()->constrained('master_distributors')->nullOnDelete();
            $table->string('distributor_nama', 150)->nullable();

            // Alur Pengiriman & Tindakan
            $table->date('tanggal_kirim')->nullable();
            $table->text('tindakan')->nullable();

            // Selesai & Kembali
            $table->date('tanggal_selesai_klaim')->nullable();
            $table->date('tanggal_barang_kembali')->nullable();

            // Penerima Barang (Pegawai)
            $table->foreignId('penerima_pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->string('penerima_nama', 150)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cabang_id');
            $table->index('tanggal_selesai_klaim');
            $table->index('tanggal_masuk');
            $table->index('tanggal_kirim');
            $table->index('no_klaim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klaim_garansis');
        Schema::dropIfExists('master_distributors');
    }
};

