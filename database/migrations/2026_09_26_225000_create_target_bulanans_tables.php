<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_divisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun')->index();
            $table->string('divisi', 50); // 'laptop', 'printer', 'komputer', 'admin'
            $table->unsignedInteger('target_bulanan')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['cabang_id', 'tahun', 'divisi']);
        });

        Schema::create('target_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun')->index();
            $table->unsignedInteger('target_bulanan')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['cabang_id', 'user_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_users');
        Schema::dropIfExists('target_divisis');
    }
};
