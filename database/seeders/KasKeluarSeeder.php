<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\SumberDana;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KasKeluarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Sumber Dana Standar Ada
        $sumberKas = SumberDana::firstOrCreate(
            ['nama' => 'Kas Laci Kasir'],
            ['is_aktif' => true]
        );
        $sumberBca = SumberDana::firstOrCreate(
            ['nama' => 'Transfer BCA (Toko)'],
            ['is_aktif' => true]
        );

        // 2. Pastikan Kategori Pengeluaran Ada
        $katListrik = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Listrik & Utilitas'],
            ['is_aktif' => true]
        );
        $katOperasional = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Operasional & ATK'],
            ['is_aktif' => true]
        );
        $katKonsumsi = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Konsumsi Staf'],
            ['is_aktif' => true]
        );
        $katMaintenance = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Maintenance & Servis Alat'],
            ['is_aktif' => true]
        );

        $cabangs = Cabang::all();
        if ($cabangs->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($cabangs as $cabang) {
            $user = User::where('cabang_id', $cabang->id)->first() ?? User::first();
            if (! $user) {
                continue;
            }

            // Hapus data kas keluar cabang ini agar seeder idempotent
            KasKeluar::where('cabang_id', $cabang->id)->delete();

            $sampleExpenses = [
                // Hari Ini
                [
                    'tanggal' => $now->copy()->setTime(9, 15),
                    'sumber_dana_id' => $sumberKas->id,
                    'kategori_kas_keluar_id' => $katOperasional->id,
                    'keterangan' => 'Beli Kertas Thermal Struk Kasir (10 Roll) & Solasi Bening',
                    'nominal' => 65000,
                ],
                [
                    'tanggal' => $now->copy()->setTime(12, 30),
                    'sumber_dana_id' => $sumberKas->id,
                    'kategori_kas_keluar_id' => $katKonsumsi->id,
                    'keterangan' => 'Makan Siang & Es Teh Tim Kasir dan Teknisi',
                    'nominal' => 50000,
                ],
                [
                    'tanggal' => $now->copy()->setTime(15, 45),
                    'sumber_dana_id' => $sumberBca->id,
                    'kategori_kas_keluar_id' => $katListrik->id,
                    'keterangan' => 'Beli Token Listrik PLN Toko (Prabayar)',
                    'nominal' => 200000,
                ],

                // Kemarin
                [
                    'tanggal' => $now->copy()->subDay()->setTime(10, 0),
                    'sumber_dana_id' => $sumberKas->id,
                    'kategori_kas_keluar_id' => $katOperasional->id,
                    'keterangan' => 'Refill Air Galon Aqua Toko 2 Galon + Tisu Meja',
                    'nominal' => 42000,
                ],
                [
                    'tanggal' => $now->copy()->subDay()->setTime(16, 20),
                    'sumber_dana_id' => $sumberBca->id,
                    'kategori_kas_keluar_id' => $katMaintenance->id,
                    'keterangan' => 'Servis & Cuci AC Ruang Utama Toko',
                    'nominal' => 150000,
                ],

                // 3 Hari Lalu
                [
                    'tanggal' => $now->copy()->subDays(3)->setTime(11, 10),
                    'sumber_dana_id' => $sumberKas->id,
                    'kategori_kas_keluar_id' => $katOperasional->id,
                    'keterangan' => 'Beli Sapu Lantai, Plastik Sampah, & Pembersih Kaca',
                    'nominal' => 38000,
                ],

                // 1 Minggu Lalu
                [
                    'tanggal' => $now->copy()->subDays(7)->setTime(14, 0),
                    'sumber_dana_id' => $sumberBca->id,
                    'kategori_kas_keluar_id' => $katOperasional->id,
                    'keterangan' => 'Iuran Internet Fiber Indihome Bulanan',
                    'nominal' => 375000,
                ],

                // Bulan Lalu
                [
                    'tanggal' => $now->copy()->subMonth()->startOfMonth()->setTime(13, 0),
                    'sumber_dana_id' => $sumberKas->id,
                    'kategori_kas_keluar_id' => $katKonsumsi->id,
                    'keterangan' => 'Snack & Kopi Rapat Evaluasi Bulanan Staf',
                    'nominal' => 85000,
                ],
            ];

            foreach ($sampleExpenses as $item) {
                KasKeluar::create([
                    'cabang_id' => $cabang->id,
                    'user_id' => $user->id,
                    'tanggal' => $item['tanggal'],
                    'sumber_dana_id' => $item['sumber_dana_id'],
                    'kategori_kas_keluar_id' => $item['kategori_kas_keluar_id'],
                    'keterangan' => $item['keterangan'],
                    'nominal' => $item['nominal'],
                ]);
            }
        }
    }
}
