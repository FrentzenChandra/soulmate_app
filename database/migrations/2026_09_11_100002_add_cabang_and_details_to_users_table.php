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
            $table->string('username', 50)->unique()->nullable()->after('name');
            $table->foreignId('cabang_id')->nullable()->after('id')->constrained('cabangs')->nullOnDelete();
            $table->string('no_hp', 30)->nullable()->after('email');
            $table->string('status', 20)->default('aktif')->after('no_hp')->comment('aktif / nonaktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn(['username', 'cabang_id', 'no_hp', 'status']);
        });
    }
};
