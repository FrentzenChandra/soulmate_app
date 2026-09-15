<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
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
        $sgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $kba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        $adminPkp = User::where('username', 'admin_pkp')->orWhere('username', 'kasir_pkp')->first()
            ?? User::where('email', 'superadmin@soulmate.test')->first();
        $adminSgl = User::where('username', 'admin_sgl')->orWhere('username', 'kasir_sgl')->first()
            ?? $adminPkp;
        $adminKba = User::where('username', 'admin_kba')->orWhere('username', 'kasir_kba')->first()
            ?? $adminPkp;

        $custHendra = Customer::where('nama', 'Hendra Setiawan')->first();
        $custMega = Customer::where('nama', 'Mega Lestari')->first();
        $custFajar = Customer::where('nama', 'PT Bangka Media Utama (Bpk. Fajar)')->first();
        $custBudi = Customer::where('nama', 'Budi Walk-In')->first();
        $custRizky = Customer::where('nama', 'Rizky Pratama')->first();

        $ppn11 = MasterPpn::where('persen', 11.00)->first();
        $tanpaPpn = MasterPpn::where('persen', 0.00)->first();

        $allProduks = Produk::all();
        $ssd = $allProduks->where('barcode', '8991234560011')->first() ?? $allProduks->first();
        $ram = $allProduks->where('barcode', '8991234560028')->first() ?? $allProduks->skip(1)->first();
        $mouse = $allProduks->where('barcode', '8991234560080')->first() ?? $allProduks->skip(2)->first();
        $pasta = $allProduks->where('barcode', '8991234560059')->first() ?? $allProduks->skip(3)->first();
        $flashdisk = $allProduks->where('barcode', '8991234560073')->first() ?? $allProduks->skip(4)->first();

        if (! $pkp || ! $adminPkp) {
            return;
        }

        $now = Carbon::now();
        $yymmdd = $now->format('ymd');

        // =========================================================================
        // DATA TRANSAKSI CABANG PANGKALPINANG (CB-PKP)
        // =========================================================================

        // 1. Transaksi Real Hari Ini (INV-PJ-CB-PKP-YYMMDD-00001)
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custHendra?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$yymmdd}-00001",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subHours(2),
            'diskon' => 20000,
            'ppn' => $ppn11,
            'is_real' => true,
            'items' => [
                ['produk' => $ssd, 'qty' => 1, 'harga' => $ssd?->harga_jual ?? 600000],
                ['produk' => $mouse, 'qty' => 1, 'harga' => $mouse?->harga_jual ?? 195000],
            ],
            'catatan' => 'Pembelian SSD NVMe upgrade dan Mouse Wireless Logitech',
        ]);

        // 2. Transaksi Real Hari Ini (INV-PJ-CB-PKP-YYMMDD-00002)
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custMega?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$yymmdd}-00002",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subHours(4),
            'diskon' => 0,
            'ppn' => $tanpaPpn,
            'is_real' => true,
            'items' => [
                ['produk' => $ram, 'qty' => 2, 'harga' => $ram?->harga_jual ?? 325000],
                ['produk' => $pasta, 'qty' => 1, 'harga' => $pasta?->harga_jual ?? 45000],
            ],
            'catatan' => 'RAM DDR4 8GB 2 keping + Thermal paste',
        ]);

        // 3. Transaksi Non-Real / Formalitas Hari Ini (INV-PJ-CB-PKP-YYMMDD-00003)
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custFajar?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$yymmdd}-00003",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subHours(5),
            'diskon' => 50000,
            'ppn' => $ppn11,
            'is_real' => false,
            'items' => [
                ['produk' => $ssd, 'qty' => 3, 'harga' => $ssd?->harga_jual ?? 600000],
                ['produk' => $flashdisk, 'qty' => 5, 'harga' => $flashdisk?->harga_jual ?? 85000],
            ],
            'catatan' => 'Nota formalitas pengadaan kantor PT Bangka Media Utama',
        ]);

        // 4. Transaksi Real Kemarin
        $yesterdayYmd = $now->copy()->subDay()->format('ymd');
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custBudi?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$yesterdayYmd}-00001",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subDay()->setHour(14)->setMinute(30),
            'diskon' => 0,
            'ppn' => $tanpaPpn,
            'is_real' => true,
            'items' => [
                ['produk' => $mouse, 'qty' => 2, 'harga' => $mouse?->harga_jual ?? 195000],
            ],
            'catatan' => 'Pelanggan walk-in',
        ]);

        // 5. Transaksi Non-Real Kemarin
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custHendra?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$yesterdayYmd}-00002",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subDay()->setHour(16)->setMinute(15),
            'diskon' => 10000,
            'ppn' => $tanpaPpn,
            'is_real' => false,
            'items' => [
                ['produk' => $ram, 'qty' => 1, 'harga' => $ram?->harga_jual ?? 325000],
            ],
            'catatan' => 'Simulasi nota cetak',
        ]);

        // 6. Transaksi Real 5 Hari Lalu
        $pastYmd = $now->copy()->subDays(5)->format('ymd');
        $this->createSale([
            'cabang_id' => $pkp->id,
            'kasir_id' => $adminPkp->id,
            'customer_id' => $custFajar?->id,
            'no_transaksi' => "INV-PJ-CB-PKP-{$pastYmd}-00001",
            'tipe_transaksi' => 'langsung',
            'tanggal' => $now->copy()->subDays(5)->setHour(10)->setMinute(20),
            'diskon' => 30000,
            'ppn' => $ppn11,
            'is_real' => true,
            'items' => [
                ['produk' => $ssd, 'qty' => 2, 'harga' => $ssd?->harga_jual ?? 600000],
                ['produk' => $ram, 'qty' => 2, 'harga' => $ram?->harga_jual ?? 325000],
            ],
            'catatan' => 'Upgrade 2 unit PC kantor',
        ]);

        // =========================================================================
        // DATA TRANSAKSI CABANG SUNGAILIAT (CB-SGL)
        // =========================================================================
        if ($sgl) {
            // 7. Transaksi Real Sungailiat Hari Ini
            $this->createSale([
                'cabang_id' => $sgl->id,
                'kasir_id' => $adminSgl->id,
                'customer_id' => $custRizky?->id,
                'no_transaksi' => "INV-PJ-CB-SGL-{$yymmdd}-00001",
                'tipe_transaksi' => 'langsung',
                'tanggal' => $now->copy()->subHours(3),
                'diskon' => 15000,
                'ppn' => $tanpaPpn,
                'is_real' => true,
                'items' => [
                    ['produk' => $ssd, 'qty' => 1, 'harga' => $ssd?->harga_jual ?? 600000],
                ],
                'catatan' => 'Penjualan kasir Sungailiat',
            ]);

            // 8. Transaksi Non-Real Sungailiat Kemarin
            $this->createSale([
                'cabang_id' => $sgl->id,
                'kasir_id' => $adminSgl->id,
                'customer_id' => $custRizky?->id,
                'no_transaksi' => "INV-PJ-CB-SGL-{$yesterdayYmd}-00001",
                'tipe_transaksi' => 'langsung',
                'tanggal' => $now->copy()->subDay()->setHour(11)->setMinute(0),
                'diskon' => 0,
                'ppn' => $tanpaPpn,
                'is_real' => false,
                'items' => [
                    ['produk' => $mouse, 'qty' => 3, 'harga' => $mouse?->harga_jual ?? 195000],
                ],
                'catatan' => 'Simulasi nota cetak cabang SKB',
            ]);
        }

        // =========================================================================
        // DATA TRANSAKSI CABANG KOBA (CB-KBA)
        // =========================================================================
        if ($kba) {
            $this->createSale([
                'cabang_id' => $kba->id,
                'kasir_id' => $adminKba->id,
                'customer_id' => null,
                'no_transaksi' => "INV-PJ-CB-KBA-{$yymmdd}-00001",
                'tipe_transaksi' => 'langsung',
                'tanggal' => $now->copy()->subHours(1),
                'diskon' => 0,
                'ppn' => $tanpaPpn,
                'is_real' => true,
                'items' => [
                    ['produk' => $pasta, 'qty' => 2, 'harga' => $pasta?->harga_jual ?? 45000],
                ],
                'catatan' => 'Penjualan kasir Koba',
            ]);
        }
    }

    private function createSale(array $data): Penjualan
    {
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += ($item['harga'] * $item['qty']);
        }

        $diskon = $data['diskon'] ?? 0;
        $dpp = max(0, $subtotal - $diskon);

        $ppnModel = $data['ppn'] ?? null;
        $ppnPersen = $ppnModel ? (float) $ppnModel->persen : 0.00;
        $ppnNominal = round($dpp * ($ppnPersen / 100), 2);
        $total = $dpp + $ppnNominal;

        $penjualan = Penjualan::updateOrCreate(
            ['no_transaksi' => $data['no_transaksi']],
            [
                'cabang_id' => $data['cabang_id'],
                'kasir_id' => $data['kasir_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'servis_id' => null,
                'tipe_transaksi' => $data['tipe_transaksi'] ?? 'langsung',
                'tanggal' => $data['tanggal'] ?? Carbon::now(),
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'ppn_id' => $ppnModel?->id,
                'ppn_persen' => $ppnPersen,
                'ppn_nominal' => $ppnNominal,
                'total' => $total,
                'jumlah_bayar' => $total,
                'kembalian' => 0,
                'metode_bayar' => 'tunai',
                'status' => 'lunas',
                'is_real' => $data['is_real'] ?? true,
                'catatan' => $data['catatan'] ?? null,
            ]
        );

        // Hapus detail lama jika ada agar bersih
        PenjualanDetail::where('penjualan_id', $penjualan->id)->delete();

        foreach ($data['items'] as $item) {
            $prod = $item['produk'];
            PenjualanDetail::create([
                'penjualan_id' => $penjualan->id,
                'tipe_item' => 'produk',
                'produk_id' => $prod?->id,
                'nama_item' => $prod?->nama_produk ?? 'Item Produk',
                'qty' => $item['qty'],
                'harga' => $item['harga'],
                'subtotal' => $item['harga'] * $item['qty'],
            ]);
        }

        return $penjualan;
    }
}