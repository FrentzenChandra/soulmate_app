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
        Schema::create('metode_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('kategori', 30)->default('tunai')->comment('tunai, transfer, qris, debit, lainnya');
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('atas_nama', 100)->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Initial Seed
        DB::table('metode_pembayarans')->insert([
            [
                'nama' => 'Tunai',
                'kategori' => 'tunai',
                'nomor_rekening' => null,
                'atas_nama' => null,
                'urutan' => 1,
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Transfer BCA',
                'kategori' => 'transfer',
                'nomor_rekening' => '0123456789',
                'atas_nama' => 'Soulmate Komputer',
                'urutan' => 2,
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'QRIS',
                'kategori' => 'qris',
                'nomor_rekening' => null,
                'atas_nama' => 'Soulmate Komputer',
                'urutan' => 3,
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Debit EDC',
                'kategori' => 'debit',
                'nomor_rekening' => null,
                'atas_nama' => null,
                'urutan' => 4,
                'is_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metode_pembayarans');
    }
};
