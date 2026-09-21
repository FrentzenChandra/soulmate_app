<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\CekKasLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CekKasLogSeeder extends Seeder
{
    public function run(): void
    {
        $cabangPkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $adminPkp = User::where('email', 'admin.pkp@soulmate.test')->first() ?? User::first();
        $kasirPkp = User::where('email', 'kasir.pkp@soulmate.test')->first() ?? $adminPkp;

        if (! $cabangPkp || ! $adminPkp) {
            return;
        }

        $yesterday = now()->subDay()->toDateString();
        $twoDaysAgo = now()->subDays(2)->toDateString();

        // Seed Sample Audit Laci Snapshot Logs
        // Snapshot 1: Kemarin Siang (Klop)
        CekKasLog::create([
            'cabang_id' => $cabangPkp->id,
            'user_id' => $kasirPkp->id,
            'tanggal' => $yesterday,
            'waktu_cek' => Carbon::parse($yesterday)->setHour(12)->setMinute(30),
            'modal_awal' => 300000,
            'penjualan_cash' => 650000,
            'servis_cash' => 200000,
            'kas_keluar_cash' => 50000,
            'kas_sistem' => 1100000,
            'kas_fisik' => 1100000,
            'selisih' => 0,
            'status_selisih' => 'klop',
            'catatan' => 'Audit pergantian shift siang',
        ]);

        // Snapshot 2: Kemarin Sore (Minus 10.000)
        CekKasLog::create([
            'cabang_id' => $cabangPkp->id,
            'user_id' => $adminPkp->id,
            'tanggal' => $yesterday,
            'waktu_cek' => Carbon::parse($yesterday)->setHour(17)->setMinute(45),
            'modal_awal' => 300000,
            'penjualan_cash' => 1250000,
            'servis_cash' => 450000,
            'kas_keluar_cash' => 100000,
            'kas_sistem' => 1900000,
            'kas_fisik' => 1890000,
            'selisih' => -10000,
            'status_selisih' => 'minus',
            'catatan' => 'Kurang uang kembalian receh',
        ]);

        // Snapshot 3: Dua Hari Lalu (Klop)
        CekKasLog::create([
            'cabang_id' => $cabangPkp->id,
            'user_id' => $kasirPkp->id,
            'tanggal' => $twoDaysAgo,
            'waktu_cek' => Carbon::parse($twoDaysAgo)->setHour(18)->setMinute(00),
            'modal_awal' => 300000,
            'penjualan_cash' => 850000,
            'servis_cash' => 300000,
            'kas_keluar_cash' => 50000,
            'kas_sistem' => 1400000,
            'kas_fisik' => 1400000,
            'selisih' => 0,
            'status_selisih' => 'klop',
            'catatan' => 'Audit penutupan kasir',
        ]);
    }
}
