<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('shift', 20)->nullable()->after('waktu_absen');
            $table->string('status_kehadiran', 30)->nullable()->after('shift');
            $table->integer('menit_keterlambatan')->default(0)->after('status_kehadiran');
            $table->time('snapshot_jam_masuk')->nullable()->after('menit_keterlambatan');
            $table->integer('snapshot_toleransi_menit')->default(2)->after('snapshot_jam_masuk');

            $table->index(['tanggal', 'status_kehadiran']);
            $table->index(['user_id', 'status_kehadiran']);
            $table->index(['cabang_id', 'tanggal', 'status_kehadiran']);
        });

        // Backfill data historis masa lalu (Opsi A)
        $records = DB::table('absensis')->whereNull('status_kehadiran')->get();
        foreach ($records as $item) {
            $waktu = Carbon::parse($item->waktu_absen, 'Asia/Jakarta');
            $jamTime = $waktu->format('H:i:s');

            if ($jamTime < '13:00:00') {
                $shift = 'pagi';
                $snapshotMasuk = '08:00:00';
                $toleransi = 2;

                if ($jamTime >= '07:00:00' && $jamTime <= '08:02:59') {
                    $status = 'tepat_waktu';
                    $menitTelat = 0;
                } else {
                    $status = 'terlambat';
                    if ($jamTime < '07:00:00') {
                        $start = $waktu->copy()->setTime(7, 0, 0);
                        $menitTelat = (int) $waktu->diffInMinutes($start, false);
                        if ($menitTelat < 0) {
                            $menitTelat = abs($menitTelat);
                        }
                    } else {
                        $target = $waktu->copy()->setTime(8, 0, 0);
                        $menitTelat = (int) $target->diffInMinutes($waktu, false);
                        if ($menitTelat < 0) {
                            $menitTelat = 0;
                        }
                    }
                }
            } else {
                $shift = 'sore';
                $snapshotMasuk = '14:00:00';
                $toleransi = 2;

                if ($jamTime >= '13:00:00' && $jamTime <= '14:02:59') {
                    $status = 'tepat_waktu';
                    $menitTelat = 0;
                } else {
                    $status = 'terlambat';
                    $target = $waktu->copy()->setTime(14, 0, 0);
                    $menitTelat = (int) $target->diffInMinutes($waktu, false);
                    if ($menitTelat < 0) {
                        $menitTelat = 0;
                    }
                }
            }

            DB::table('absensis')->where('id', $item->id)->update([
                'shift' => $shift,
                'status_kehadiran' => $status,
                'menit_keterlambatan' => $menitTelat,
                'snapshot_jam_masuk' => $snapshotMasuk,
                'snapshot_toleransi_menit' => $toleransi,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropIndex(['cabang_id', 'tanggal', 'status_kehadiran']);
            $table->dropIndex(['user_id', 'status_kehadiran']);
            $table->dropIndex(['tanggal', 'status_kehadiran']);

            $table->dropColumn([
                'shift',
                'status_kehadiran',
                'menit_keterlambatan',
                'snapshot_jam_masuk',
                'snapshot_toleransi_menit',
            ]);
        });
    }
};

