<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->string('metode_pembayaran', 30)->nullable()->after('total_biaya');
            $table->decimal('bayar', 15, 2)->nullable()->default(0)->after('metode_pembayaran');
            $table->decimal('kembalian', 15, 2)->nullable()->default(0)->after('bayar');
            $table->integer('garansi_hari')->default(0)->after('kembalian');
            $table->date('garansi_sampai')->nullable()->after('garansi_hari');
            $table->foreignId('kasir_id')->nullable()->after('teknisi_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->dropForeign(['kasir_id']);
            $table->dropColumn([
                'metode_pembayaran',
                'bayar',
                'kembalian',
                'garansi_hari',
                'garansi_sampai',
                'kasir_id',
            ]);
        });
    }
};
