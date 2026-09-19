<?php

namespace Tests\Feature;

use App\Livewire\Transaksi\Full as TransaksiFull;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\SumberDana;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransaksiFullTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_transaksi_full()
    {
        $this->get(route('transaksi.full'))->assertRedirect(route('login'));
    }

    public function test_superadmin_can_view_transaksi_full_and_net_cash_calculation()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $this->get(route('transaksi.full'))->assertStatus(200);

        $produk = Produk::first();
        $sumberDanaCash = SumberDana::where('nama', 'like', '%Cash%')->first();
        $kategoriKas = KategoriKasKeluar::first();

        // 1. Buat Penjualan Cash
        $penjualan = Penjualan::create([
            'no_transaksi' => 'INV-PJ-PKP-2609-00001',
            'cabang_id' => $superadmin->cabang_id,
            'kasir_id' => $superadmin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 200000,
            'diskon' => 0,
            'total' => 200000,
            'jumlah_bayar' => 200000,
            'kembalian' => 0,
            'metode_bayar' => 'Cash',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'tipe_item' => 'barang',
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama_produk,
            'qty' => 2,
            'harga' => 100000,
            'subtotal' => 200000,
        ]);

        // 2. Buat Kas Keluar Cash
        KasKeluar::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'sumber_dana_id' => $sumberDanaCash->id,
            'kategori_kas_keluar_id' => $kategoriKas->id,
            'tanggal' => now(),
            'nominal' => 50000,
            'keterangan' => 'Beli bensin antar barang',
        ]);

        // 3. Test Component State & Net Cash Formula
        $test = Livewire::test(TransaksiFull::class)
            ->assertSet('tipeFilter', 'semua');

        $viewData = $test->viewData('totalPenjualanCash');
        $this->assertNotNull($viewData);

        $penjualanCash = $test->viewData('totalPenjualanCash');
        $servisCash = $test->viewData('totalServisCash');
        $kasKeluarCash = $test->viewData('totalKasKeluarCash');
        $saldoKasTunaiBersih = $test->viewData('saldoKasTunaiBersih');

        $expectedSaldo = ($penjualanCash + $servisCash) - $kasKeluarCash;
        $this->assertEquals($expectedSaldo, $saldoKasTunaiBersih);
    }
}
