<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->boolean('is_cetak_nota')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('servis_masuks', function (Blueprint $table) {
            $table->dropColumn('is_cetak_nota');
        });
    }
};
