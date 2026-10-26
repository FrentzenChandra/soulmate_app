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
        Schema::table('jam_kerjas', function (Blueprint $table) {
            $table->time('jam_rentang_mulai')->default('00:00:00')->after('nama_shift');
            $table->integer('batas_kritis_menit')->default(120)->after('toleransi_menit');
        });

        // Update default data awal
        DB::table('jam_kerjas')->where('nama_shift', 'pagi')->update([
            'jam_rentang_mulai' => '00:00:00',
            'jam_cutoff' => '12:59:59',
            'jam_mulai' => '07:00:00',
            'jam_masuk' => '08:00:00',
            'toleransi_menit' => 2,
            'batas_kritis_menit' => 120,
        ]);

        DB::table('jam_kerjas')->where('nama_shift', 'sore')->update([
            'jam_rentang_mulai' => '13:00:00',
            'jam_cutoff' => '23:59:59',
            'jam_mulai' => '13:00:00',
            'jam_masuk' => '14:00:00',
            'toleransi_menit' => 2,
            'batas_kritis_menit' => 120,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jam_kerjas', function (Blueprint $table) {
            $table->dropColumn(['jam_rentang_mulai', 'batas_kritis_menit']);
        });
    }
};

