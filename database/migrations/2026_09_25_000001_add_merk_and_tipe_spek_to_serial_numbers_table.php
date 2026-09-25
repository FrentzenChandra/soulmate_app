<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serial_numbers', function (Blueprint $table) {
            if (! Schema::hasColumn('serial_numbers', 'merk')) {
                $table->string('merk')->nullable()->after('customer_id');
            }
            if (! Schema::hasColumn('serial_numbers', 'tipe_spek')) {
                $table->string('tipe_spek')->nullable()->after('merk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('serial_numbers', function (Blueprint $table) {
            $table->dropColumn(['merk', 'tipe_spek']);
        });
    }
};
