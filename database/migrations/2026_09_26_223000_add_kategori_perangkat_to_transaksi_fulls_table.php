<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->string('kategori_perangkat', 50)->nullable()->after('tipe_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->dropColumn('kategori_perangkat');
        });
    }
};
