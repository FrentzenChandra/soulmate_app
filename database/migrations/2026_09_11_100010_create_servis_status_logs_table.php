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
        Schema::create('servis_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servis_id')->constrained('servis_masuks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->comment('pengecekan, pengerjaan, selesai, sudah_dibayar, batal');
            $table->dateTime('tanggal');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['servis_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servis_status_logs');
    }
};
