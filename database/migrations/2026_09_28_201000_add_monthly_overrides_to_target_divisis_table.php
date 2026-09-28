<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_divisis', function (Blueprint $table) {
            $table->json('monthly_targets')->nullable()->after('target_bulanan');
            $table->json('monthly_realisasi')->nullable()->after('monthly_targets');
        });
    }

    public function down(): void
    {
        Schema::table('target_divisis', function (Blueprint $table) {
            $table->dropColumn(['monthly_targets', 'monthly_realisasi']);
        });
    }
};
