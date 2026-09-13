<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\NomorUrutTransaksi;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $kasirPkp = User::where('username', 'kasir_pkp')->first();
        $teknisiPkp = User::where('username', 'teknisi_pkp')->first();
        $customer = Customer::where('nama', 'Hendra Setiawan')->first();
        $customer2 = Customer::where('nama', 'Mega Lestari')->first();

        $ppn11 = MasterPpn::where('persen', 11.00)->first();
        $tanpaPpn = MasterPpn::where('persen', 0.00)->first();

        $ssd = Produk::where('barcode', '8991234560011')->first();
        $ram = Produk::where('barcode', '8991234560028')->first();
        $mouse = Produk::where('barcode', '8991234560080')->first();
        $pasta = Produk::where('barcode', '8991234560059')->first();

        if (! $pkp || ! $kasirPkp) {
            return;
        }

        $now = Carbon::now();

        // 1. Transaksi Penjualan Barang Kasir POS (INV-PJ-000001)
        $subtotal1 = ($ssd ? $ssd->harga_jual : 600000) + ($mouse ? $mouse->harga_jual : 195000);
        $diskon1 = 20000;
        $dpp1 = $subtotal1 - $diskon1;
        $ppnNominal1 = round($dpp1 * 0.11, 2);
        $total1 = $dpp1 + $ppnNominal1;

        $pj1 = Penjualan::updateOrCreate(
            ['no_transaksi' => 'INV-PJ-000001'],
            [
                'cabang_id' => $pkp->id,
                'kasir_id' => $kasirPkp->id,
                'customer_id' => $customer?->id,
                'servis_id' => null,
                'tipe_transaksi' => 'penjualan_barang',
                'tanggal' => $now->copy()->subHours(8),
                'subtotal' => $subtotal1,
                'diskon' => $diskon1,
                'ppn_id' => $ppn11?->id,
                'ppn_persen' => 11.00,
                'ppn_nominal' => $ppnNominal1,
                'total' => $total1,
                'jumlah_bayar' => 870000,
                'kembalian' => 870000 - $total1,
                'metode_bayar' => 'qris',
                'status' => 'lunas',
                'catatan' => 'Pembelian aksesoris & SSD upgrade',
            ]
        );

        if ($ssd) {
            PenjualanDetail::firstOrCreate([
                'penjualan_id' => $pj1->id,
                'tipe_item' => 'barang',
                'produk_id' => $ssd->id,
                'nama_item' => $ssd->nama_produk,
                'qty' => 1,
                'harga' => $ssd->harga_jual,
                'subtotal' => $ssd->harga_jual,
            ]);
        }
        if ($mouse) {
            PenjualanDetail::firstOrCreate([
                'penjualan_id' => $pj1->id,
                'tipe_item' => 'barang',
                'produk_id' => $mouse->id,
                'nama_item' => $mouse->nama_produk,
                'qty' => 1,
                'harga' => $mouse->harga_jual,
                'subtotal' => $mouse->harga_jual,
            ]);
        }

        // 2. Transaksi Penagihan Servis Selesai & Lunas (INV-SV-000001)
        // Kita buat 1 unit servis yang statusnya sudah_dibayar & terkunci
        $svLunas = ServisMasuk::updateOrCreate(
            ['no_servis' => 'SRV-PKP-2609000'],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer2->id,
                'teknisi_id' => $teknisiPkp?->id ?? $kasirPkp->id,
                'created_by' => $kasirPkp->id,
                'kategori_perangkat' => 'laptop',
                'tipe_unit' => 'Acer Aspire 5 A514',
                'serial_number' => 'NXH9912001',
                'kelengkapan' => 'Unit + Charger',
                'keluhan' => 'Laptop lemot, ingin upgrade SSD + RAM dan instal ulang OS Windows 11',
                'kondisi_barang' => 'Mulus',
                'status' => 'sudah_dibayar',
                'tanggal_masuk' => $now->copy()->subDays(5),
                'tanggal_selesai' => $now->copy()->subDays(1),
                'tanggal_ambil' => $now->copy()->subHours(2),
                'estimasi_biaya' => 1050000,
                'total_biaya' => 1045000,
                'catatan' => 'Selesai upgrade dan sudah diambil customer',
            ]
        );

        ServisDetail::firstOrCreate([
            'servis_id' => $svLunas->id,
            'nama_item' => 'SSD NVMe M.2 Kingston NV2 512GB PCIe 4.0',
            'produk_id' => $ssd?->id,
            'qty' => 1,
            'harga' => 600000,
            'subtotal' => 600000,
        ]);
        ServisDetail::firstOrCreate([
            'servis_id' => $svLunas->id,
            'nama_item' => 'RAM Sodimm DDR4 Kingston 8GB 3200MHz',
            'produk_id' => $ram?->id,
            'qty' => 1,
            'harga' => 325000,
            'subtotal' => 325000,
        ]);
        ServisDetail::firstOrCreate([
            'servis_id' => $svLunas->id,
            'nama_item' => 'Jasa Instalasi OS Windows 11 + Aplikasi Standar',
            'produk_id' => null,
            'qty' => 1,
            'harga' => 120000,
            'subtotal' => 120000,
        ]);

        ServisStatusLog::firstOrCreate([
            'servis_id' => $svLunas->id,
            'user_id' => $kasirPkp->id,
            'status' => 'sudah_dibayar',
            'tanggal' => $now->copy()->subHours(2),
            'catatan' => 'Pembayaran lunas via kasir dan unit diserahkan kepada customer',
        ]);

        $subtotalSv = 1045000;
        $diskonSv = 45000;
        $totalSv = $subtotalSv - $diskonSv;

        $pjSv = Penjualan::updateOrCreate(
            ['no_transaksi' => 'INV-SV-000001'],
            [
                'cabang_id' => $pkp->id,
                'kasir_id' => $kasirPkp->id,
                'customer_id' => $customer2->id,
                'servis_id' => $svLunas->id,
                'tipe_transaksi' => 'pembayaran_servis',
                'tanggal' => $now->copy()->subHours(2),
                'subtotal' => $subtotalSv,
                'diskon' => $diskonSv,
                'ppn_id' => $tanpaPpn?->id,
                'ppn_persen' => 0.00,
                'ppn_nominal' => 0.00,
                'total' => $totalSv,
                'jumlah_bayar' => 1000000,
                'kembalian' => 0,
                'metode_bayar' => 'transfer',
                'status' => 'lunas',
                'catatan' => 'Pelunasan tagihan servis Acer Aspire 5 A514',
            ]
        );

        PenjualanDetail::firstOrCreate([
            'penjualan_id' => $pjSv->id,
            'tipe_item' => 'barang',
            'produk_id' => $ssd?->id,
            'nama_item' => 'SSD NVMe M.2 Kingston NV2 512GB PCIe 4.0',
            'qty' => 1,
            'harga' => 600000,
            'subtotal' => 600000,
        ]);
        PenjualanDetail::firstOrCreate([
            'penjualan_id' => $pjSv->id,
            'tipe_item' => 'barang',
            'produk_id' => $ram?->id,
            'nama_item' => 'RAM Sodimm DDR4 Kingston 8GB 3200MHz',
            'qty' => 1,
            'harga' => 325000,
            'subtotal' => 325000,
        ]);
        PenjualanDetail::firstOrCreate([
            'penjualan_id' => $pjSv->id,
            'tipe_item' => 'jasa_servis',
            'produk_id' => null,
            'nama_item' => 'Jasa Instalasi OS Windows 11 + Aplikasi Standar',
            'qty' => 1,
            'harga' => 120000,
            'subtotal' => 120000,
        ]);

        // Inisialisasi Counter Nomor Urut
        NomorUrutTransaksi::updateOrCreate(
            [
                'cabang_id' => $pkp->id,
                'tipe' => 'penjualan_barang',
                'tahun' => (int) $now->format('Y'),
            ],
            [
                'bulan' => (int) $now->format('m'),
                'last_number' => 1,
            ]
        );

        NomorUrutTransaksi::updateOrCreate(
            [
                'cabang_id' => $pkp->id,
                'tipe' => 'pembayaran_servis',
                'tahun' => (int) $now->format('Y'),
            ],
            [
                'bulan' => (int) $now->format('m'),
                'last_number' => 1,
            ]
        );
    }
}
