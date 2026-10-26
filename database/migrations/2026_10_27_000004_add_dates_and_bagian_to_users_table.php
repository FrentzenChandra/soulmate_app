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
        Schema::table('users', function (Blueprint $table) {
            $table->date('tanggal_mulai_bekerja')->nullable()->after('cabang_id');
            $table->date('tanggal_lahir')->nullable()->after('tanggal_mulai_bekerja');
            $table->string('bagian', 50)->nullable()->after('tanggal_lahir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai_bekerja', 'tanggal_lahir', 'bagian']);
        });
    }
};
