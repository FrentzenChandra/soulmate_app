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
        Schema::table('transaksi_full_details', function (Blueprint $table) {
            $table->foreignId('ppn_id')->nullable()->after('laba')->constrained('master_ppns')->nullOnDelete();
            $table->decimal('ppn_persen', 5, 2)->default(0)->after('ppn_id');
            $table->decimal('ppn_nominal', 15, 2)->default(0)->after('ppn_persen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_full_details', function (Blueprint $table) {
            $table->dropForeign(['ppn_id']);
            $table->dropColumn(['ppn_id', 'ppn_persen', 'ppn_nominal']);
        });
    }
};
