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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->string('nama', 150);
            $table->string('no_hp', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('tipe', 20)->default('langganan')->comment('umum / langganan');
            $table->string('foto_url')->nullable();
            $table->timestamps();

            $table->index(['cabang_id', 'no_hp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
