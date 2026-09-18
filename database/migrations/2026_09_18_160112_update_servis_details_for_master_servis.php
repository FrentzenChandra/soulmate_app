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
        Schema::table('servis_details', function (Blueprint $table) {
            $table->foreignId('master_servis_id')->nullable()->after('servis_id')->constrained('master_servis')->nullOnDelete();
            $table->enum('jenis', ['jasa', 'sparepart'])->default('jasa')->after('nama_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servis_details', function (Blueprint $table) {
            $table->dropForeign(['master_servis_id']);
            $table->dropColumn(['master_servis_id', 'jenis']);
        });
    }
};
