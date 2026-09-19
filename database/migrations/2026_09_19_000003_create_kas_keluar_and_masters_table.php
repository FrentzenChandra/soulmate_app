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
        // 1. Master Sumber Dana (Cash, BCA, TF BCA, dll)
        Schema::create('sumber_danas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        DB::table('sumber_danas')->insert([
            ['nama' => 'Cash', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'BCA', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'TF BCA', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Master Kategori Kas Keluar
        Schema::create('kategori_kas_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        DB::table('kategori_kas_keluars')->insert([
            ['nama' => 'Operasional', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Listrik Air', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Konsumsi', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'ATK', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Maintenance', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Lainnya', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Transaksi Kas Keluar
        Schema::create('kas_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->foreignId('kategori_kas_keluar_id')->nullable()->constrained('kategori_kas_keluars')->nullOnDelete();
            $table->timestamp('tanggal')->useCurrent();
            $table->string('keterangan', 255);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_keluars');
        Schema::dropIfExists('kategori_kas_keluars');
        Schema::dropIfExists('sumber_danas');
    }
};
