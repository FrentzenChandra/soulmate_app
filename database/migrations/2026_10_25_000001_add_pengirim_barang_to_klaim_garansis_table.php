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
        Schema::table('klaim_garansis', function (Blueprint $table) {
            $table->foreignId('pengirim_pegawai_id')
                ->nullable()
                ->after('tanggal_kirim')
                ->constrained('pegawais')
                ->nullOnDelete();
            $table->string('pengirim_nama', 150)
                ->nullable()
                ->after('pengirim_pegawai_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klaim_garansis', function (Blueprint $table) {
            $table->dropForeign(['pengirim_pegawai_id']);
            $table->dropColumn(['pengirim_pegawai_id', 'pengirim_nama']);
        });
    }
};

