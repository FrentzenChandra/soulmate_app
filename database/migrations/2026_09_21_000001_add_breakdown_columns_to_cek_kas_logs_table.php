<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cek_kas_logs', function (Blueprint $table) {
            $table->decimal('modal_awal', 15, 2)->default(0)->after('waktu_cek');
            $table->decimal('penjualan_cash', 15, 2)->default(0)->after('modal_awal');
            $table->decimal('servis_cash', 15, 2)->default(0)->after('penjualan_cash');
            $table->decimal('kas_keluar_cash', 15, 2)->default(0)->after('servis_cash');
        });
    }

    public function down(): void
    {
        Schema::table('cek_kas_logs', function (Blueprint $table) {
            $table->dropColumn(['modal_awal', 'penjualan_cash', 'servis_cash', 'kas_keluar_cash']);
        });
    }
};
