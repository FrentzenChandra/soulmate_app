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
        Schema::create('master_servis_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_servis_id')->constrained('master_servis')->cascadeOnDelete();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->integer('min_stok')->default(3);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->unique(['master_servis_id', 'cabang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_servis_stoks');
    }
};
