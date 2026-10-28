<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_gajis', function (Blueprint $table) {
            if (! Schema::hasColumn('slip_gajis', 'alasan_buka_kembali')) {
                $table->text('alasan_buka_kembali')->nullable()->after('finalized_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('slip_gajis', function (Blueprint $table) {
            if (Schema::hasColumn('slip_gajis', 'alasan_buka_kembali')) {
                $table->dropColumn('alasan_buka_kembali');
            }
        });
    }
};

