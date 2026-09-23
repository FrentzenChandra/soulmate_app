<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->dateTime('tanggal_lunas')->nullable()->after('tanggal')->index();
        });

        // Set tanggal_lunas = tanggal untuk transaksi yang statusnya sudah Lunas
        DB::table('transaksi_fulls')
            ->where('status', 'Lunas')
            ->whereNull('tanggal_lunas')
            ->update([
                'tanggal_lunas' => DB::raw('tanggal'),
            ]);
    }

    public function down(): void
    {
        Schema::table('transaksi_fulls', function (Blueprint $table) {
            $table->dropIndex(['tanggal_lunas']);
            $table->dropColumn('tanggal_lunas');
        });
    }
};
