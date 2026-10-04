<?php

namespace Tests\Feature;

use App\Livewire\Transaksi\Full as TransaksiFull;
use App\Models\MasterPpn;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\TransaksiFull as TransaksiFullModel;
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

    public function test_admin_cannot_access_transaksi_full()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $this->get(route('transaksi.full'))->assertStatus(403);
    }

    public function test_superadmin_can_create_transaksi_full_without_mutating_stock()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $produk = Produk::first();
        $stokAwal = Stok::where('produk_id', $produk->id)->where('cabang_id', $superadmin->cabang_id)->value('jumlah') ?? 50;

        $metode = MetodePembayaran::first();
        $ppn = MasterPpn::first();

        $test = Livewire::test(TransaksiFull::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Budi Santoso')
            ->set('formNoHp', '08123456789')
            ->set('formTipeTransaksi', 'penjualan')
            ->set('formStatus', 'Lunas')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama_metode)
            ->set('formPpnId', $ppn->id)
            ->set('formPpnPersen', $ppn->persentase)
            ->call('selectProductForItem', 0, $produk->id, $produk->nama_produk, 100000, 150000)
            ->set('items.0.qty', 2)
            ->call('recalculateRow', 0)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'nama_pelanggan' => 'Budi Santoso',
            'tipe_transaksi' => 'penjualan',
            'status' => 'Lunas',
            'grand_total' => 300000 + (300000 * ($ppn->persentase / 100)),
        ]);

        // Pastikan stok master produk TIDAK berubah sama sekali (Zero Stock Mutation)
        $stokAkhir = Stok::where('produk_id', $produk->id)->where('cabang_id', $superadmin->cabang_id)->value('jumlah') ?? 50;
        $this->assertEquals($stokAwal, $stokAkhir, 'Transaksi Full must never mutate stock.');
    }

    public function test_inline_status_dropdown_update()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'pegawai_id' => null,
            'nama_pelanggan' => 'Andi Test',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00001',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->toDateString(),
            'total_modal' => 50000,
            'total_jual' => 80000,
            'total_laba' => 30000,
            'grand_total' => 80000,
            'status' => 'BON',
        ]);

        Livewire::test(TransaksiFull::class)
            ->call('updateStatusDirect', $transaksi->id, 'Lunas');

        $this->assertEquals('Lunas', $transaksi->fresh()->status);
    }

    public function test_update_and_delete_transaksi_full()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'pegawai_id' => null,
            'nama_pelanggan' => 'Target Edit',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00002',
            'tipe_transaksi' => 'servis',
            'tanggal' => now()->toDateString(),
            'total_modal' => 20000,
            'total_jual' => 50000,
            'total_laba' => 30000,
            'grand_total' => 50000,
            'status' => 'BON',
        ]);

        $transaksi->details()->create([
            'nama_item' => 'Jasa Servis Keyboard',
            'qty' => 1,
            'harga_modal' => 20000,
            'harga_jual' => 50000,
            'subtotal_modal' => 20000,
            'subtotal_jual' => 50000,
            'laba' => 30000,
        ]);

        // Edit
        Livewire::test(TransaksiFull::class)
            ->call('openEditModal', $transaksi->id)
            ->set('formNamaPelanggan', 'Target Edit Updated')
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertEquals('Target Edit Updated', $transaksi->fresh()->nama_pelanggan);

        // Delete
        Livewire::test(TransaksiFull::class)
            ->call('confirmDelete', $transaksi->id, $transaksi->no_transaksi_frontend)
            ->call('deleteTransaksi');

        $this->assertDatabaseMissing('transaksi_fulls', ['id' => $transaksi->id]);
    }

    public function test_period_filter_modes_switches_dates_correctly()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TransaksiFull::class)
            ->assertSet('periodeMode', 'range')
            // ->assertSet('startDate', now()->toDateString())
            ->assertSet('startDate', now()->startOfMonth()->toDateString())
            ->assertSet('endDate', now()->toDateString())
            ->call('setPeriodeMode', 'bulanan')
            ->assertSet('periodeMode', 'bulanan')
            ->assertSet('startDate', now()->startOfMonth()->toDateString())
            ->assertSet('endDate', now()->endOfMonth()->toDateString())
            ->call('setPeriodeMode', 'tahunan')
            ->assertSet('periodeMode', 'tahunan')
            ->assertSet('startDate', now()->startOfYear()->toDateString())
            ->assertSet('endDate', now()->endOfYear()->toDateString())
            ->call('setPeriodeMode', 'range')
            ->assertSet('periodeMode', 'range')
            ->assertSet('startDate', now()->startOfMonth()->toDateString())
            ->assertSet('endDate', now()->toDateString());
    }

    public function test_card_metrics_locked_to_current_month_to_date()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        TransaksiFullModel::query()->delete();

        $ppn = MasterPpn::where('persen', '>', 0)->first();
        $ppnId = $ppn ? $ppn->id : 2;
        $ppnPersen = $ppn ? (float) $ppn->persen : 11.00;

        // 1. Transaksi dengan PPN
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'pegawai_id' => null,
            'nama_pelanggan' => 'Pelanggan PPN',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00010',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->toDateString(),
            'ppn_id' => $ppnId,
            'ppn_persen' => $ppnPersen,
            'ppn_nominal' => 11000,
            'total_modal' => 70000,
            'total_jual' => 100000,
            'total_laba' => 30000,
            'grand_total' => 111000,
            'status' => 'Lunas',
        ]);

        // 2. Transaksi Non-PPN
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'pegawai_id' => null,
            'nama_pelanggan' => 'Pelanggan Non PPN',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00011',
            'tipe_transaksi' => 'servis',
            'tanggal' => now()->toDateString(),
            'ppn_id' => null,
            'ppn_persen' => 0,
            'ppn_nominal' => 0,
            'total_modal' => 20000,
            'total_jual' => 50000,
            'total_laba' => 30000,
            'grand_total' => 50000,
            'status' => 'Lunas',
        ]);

        // Test Filter PPN (5 Card tetap terkunci ke total bulan berjalan 150k, 90k, 60k)
        Livewire::test(TransaksiFull::class)
            ->set('ppnFilter', 'ppn')
            ->assertViewHas('totalPendapatan', 150000.0)
            ->assertViewHas('totalModal', 90000.0)
            ->assertViewHas('totalLaba', 60000.0)
            ->assertViewHas('totalLunas', 150000.0)
            // Test Filter Non-PPN
            ->set('ppnFilter', 'non_ppn')
            ->assertViewHas('totalPendapatan', 150000.0)
            ->assertViewHas('totalModal', 90000.0)
            ->assertViewHas('totalLaba', 60000.0)
            // Test Filter Servis saja
            ->set('ppnFilter', 'semua')
            ->set('tipeFilter', 'servis')
            ->assertViewHas('totalPendapatan', 150000.0)
            ->assertViewHas('totalModal', 90000.0)
            ->assertViewHas('totalLaba', 60000.0);
    }

    public function test_can_create_and_edit_servis_transaksi_with_device_category()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $metode = MetodePembayaran::first();

        // 1. Create Servis Transaksi with kategori 'printer'
        $test = Livewire::test(TransaksiFull::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Joko Servis Printer')
            ->set('formTipeTransaksi', 'servis')
            ->set('formKategoriPerangkat', 'printer')
            ->set('formStatus', 'Lunas')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama)
            ->set('items.0.nama_item', 'Servis Head Printer Epson L3110')
            ->set('items.0.qty', 1)
            ->set('items.0.harga_modal', 50000)
            ->set('items.0.harga_jual', 150000)
            ->call('recalculateRow', 0)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'nama_pelanggan' => 'Joko Servis Printer',
            'tipe_transaksi' => 'servis',
            'kategori_perangkat' => 'printer',
            'status' => 'Lunas',
            'grand_total' => 150000,
        ]);

        $trx = TransaksiFullModel::where('nama_pelanggan', 'Joko Servis Printer')->first();
        $this->assertEquals('Printer', $trx->kategori_perangkat_label);

        // 2. Edit Servis Transaksi to kategori 'laptop'
        Livewire::test(TransaksiFull::class)
            ->call('openEditModal', $trx->id)
            ->assertSet('formKategoriPerangkat', 'printer')
            ->set('formKategoriPerangkat', 'laptop')
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'id' => $trx->id,
            'kategori_perangkat' => 'laptop',
        ]);
    }

    public function test_can_create_and_edit_transaksi_with_null_pegawai()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $metode = MetodePembayaran::first();

        // 1. Create with formPegawaiId = null
        Livewire::test(TransaksiFull::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Pelanggan Tanpa Pegawai')
            ->set('formPegawaiId', null)
            ->set('formTipeTransaksi', 'penjualan')
            ->set('formStatus', 'Lunas')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama)
            ->set('items.0.nama_item', 'Mouse Wireless')
            ->set('items.0.qty', 1)
            ->set('items.0.harga_modal', 50000)
            ->set('items.0.harga_jual', 75000)
            ->call('recalculateRow', 0)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'nama_pelanggan' => 'Pelanggan Tanpa Pegawai',
            'pegawai_id' => null,
        ]);

        $trx = TransaksiFullModel::where('nama_pelanggan', 'Pelanggan Tanpa Pegawai')->first();
        $this->assertNull($trx->pegawai_id);

        // 2. Open edit modal and ensure formPegawaiId remains null
        Livewire::test(TransaksiFull::class)
            ->call('openEditModal', $trx->id)
            ->assertSet('formPegawaiId', null)
            ->set('formNamaPelanggan', 'Pelanggan Tanpa Pegawai Updated')
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'id' => $trx->id,
            'nama_pelanggan' => 'Pelanggan Tanpa Pegawai Updated',
            'pegawai_id' => null,
        ]);
    }

    public function test_inline_metode_pembayaran_direct_update()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $metodeCash = MetodePembayaran::create([
            'nama' => 'Cash Tunai',
            'cabang_id' => $superadmin->cabang_id,
            'is_aktif' => true,
        ]);
        $metodeTransfer = MetodePembayaran::create([
            'nama' => 'Transfer BCA',
            'cabang_id' => $superadmin->cabang_id,
            'is_aktif' => true,
        ]);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'no_transaksi' => 'INV-PJ-DUMMY-001',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'pegawai_id' => null,
            'nama_pelanggan' => 'Test Inline Metode',
            'metode_pembayaran_id' => $metodeCash->id,
            'metode_pembayaran' => $metodeCash->nama,
            'grand_total' => 100000,
            'total_modal' => 80000,
            'total_laba' => 20000,
            'status' => 'Lunas',
        ]);

        Livewire::test(TransaksiFull::class)
            ->call('updateMetodeDirect', $transaksi->id, $metodeTransfer->id);

        $this->assertEquals($metodeTransfer->id, $transaksi->fresh()->metode_pembayaran_id);
        $this->assertEquals($metodeTransfer->nama, $transaksi->fresh()->metode_pembayaran);

        // Nullify metode
        Livewire::test(TransaksiFull::class)
            ->call('updateMetodeDirect', $transaksi->id, null);

        $this->assertNull($transaksi->fresh()->metode_pembayaran_id);
        $this->assertEquals('-', $transaksi->fresh()->metode_pembayaran);
    }

    public function test_can_create_multi_item_transaksi_with_per_item_ppn()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $ppn11 = MasterPpn::firstOrCreate(['nama' => 'PPN 11%'], [
            'persen' => 11,
            'is_aktif' => true,
        ]);

        $metode = MetodePembayaran::firstOrCreate(['nama' => 'Cash', 'cabang_id' => $superadmin->cabang_id], [
            'is_aktif' => true,
        ]);

        $test = Livewire::test(TransaksiFull::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Pelanggan PPN Mix')
            ->set('formTipeTransaksi', 'penjualan')
            ->set('formStatus', 'Lunas')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama)
            // Item 1: Jasa Servis (Tanpa PPN)
            ->set('items.0.nama_item', 'Jasa Install Windows')
            ->set('items.0.harga_modal', 0)
            ->set('items.0.harga_jual', 50000)
            ->set('items.0.qty', 1)
            ->set('items.0.ppn_id', null)
            ->call('recalculateRow', 0)
            // Item 2: Sparepart (Pakai PPN 11%)
            ->call('addItemRow')
            ->set('items.1.nama_item', 'SSD 256GB')
            ->set('items.1.harga_modal', 200000)
            ->set('items.1.harga_jual', 300000)
            ->set('items.1.qty', 1)
            ->set('items.1.ppn_id', $ppn11->id)
            ->call('recalculateRow', 1)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $trx = TransaksiFullModel::where('nama_pelanggan', 'Pelanggan PPN Mix')->first();
        $this->assertNotNull($trx);
        $this->assertEquals(350000, $trx->total_jual);
        $this->assertEquals(33000, $trx->ppn_nominal); // 11% dari 300.000 = 33.000
        $this->assertEquals(383000, $trx->grand_total); // 350.000 + 33.000 = 383.000

        $details = $trx->details()->orderBy('id')->get();
        $this->assertCount(2, $details);

        // Item 1 Detail
        $this->assertEquals('Jasa Install Windows', $details[0]->nama_item);
        $this->assertNull($details[0]->ppn_id);
        $this->assertEquals(0, $details[0]->ppn_nominal);

        // Item 2 Detail
        $this->assertEquals('SSD 256GB', $details[1]->nama_item);
        $this->assertEquals($ppn11->id, $details[1]->ppn_id);
        $this->assertEquals(33000, $details[1]->ppn_nominal);
    }

    public function test_audit_user_input_and_pelunas_tracking()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $metode = MetodePembayaran::firstOrCreate(['nama' => 'Cash', 'cabang_id' => $superadmin->cabang_id], [
            'is_aktif' => true,
        ]);

        // 1. Create BON transaction
        Livewire::test(TransaksiFull::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Pelanggan Audit Test')
            ->set('formTipeTransaksi', 'penjualan')
            ->set('formStatus', 'Bon')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama)
            ->set('items.0.nama_item', 'Barang BON')
            ->set('items.0.harga_modal', 10000)
            ->set('items.0.harga_jual', 20000)
            ->set('items.0.qty', 1)
            ->call('recalculateRow', 0)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $trx = TransaksiFullModel::where('nama_pelanggan', 'Pelanggan Audit Test')->first();
        $this->assertNotNull($trx);
        $this->assertEquals($superadmin->id, $trx->user_id);
        $this->assertNull($trx->pelunas_user_id);
        $this->assertNull($trx->tanggal_lunas);

        // 2. Direct update from BON to Lunas
        Livewire::test(TransaksiFull::class)
            ->call('updateStatusDirect', $trx->id, 'Lunas');

        $trxFresh = $trx->fresh();
        $this->assertEquals('Lunas', $trxFresh->status);
        $this->assertEquals($superadmin->id, $trxFresh->pelunas_user_id);
        $this->assertNotNull($trxFresh->tanggal_lunas);

        // 3. Direct update back to BON
        Livewire::test(TransaksiFull::class)
            ->call('updateStatusDirect', $trx->id, 'Bon');

        $trxBonAgain = $trx->fresh();
        $this->assertEquals('Bon', $trxBonAgain->status);
        $this->assertNull($trxBonAgain->pelunas_user_id);
        $this->assertNull($trxBonAgain->tanggal_lunas);
    }

    public function test_uang_cash_metric_scoped_to_running_month_and_includes_cross_month_bon_settlement()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        // Ambil baseline cash dari seeder
        $initialCash = 0;
        Livewire::test(TransaksiFull::class)->assertViewHas('totalUangCash', function ($val) use (&$initialCash) {
            $initialCash = (float) $val;
            return true;
        });

        // 1. Modal awal bulan berjalan (500.000)
        \App\Models\ModalAwal::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nominal' => 500000,
            'tanggal' => now(),
            'catatan' => 'Modal kasir hari ini',
        ]);

        // 2. Kas keluar cash bulan berjalan (50.000)
        \App\Models\KasKeluar::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nominal' => 50000,
            'tanggal' => now(),
            'keterangan' => 'Beli ATK Cash',
        ]);

        // 3. Transaksi Cash Lunas Bulan Berjalan (100.000)
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Cash Oktober',
            'no_transaksi' => 'PJ-OKT-001',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'tanggal_lunas' => now(),
            'metode_pembayaran' => 'Tunai',
            'total_modal' => 60000,
            'total_jual' => 100000,
            'grand_total' => 100000,
            'total_laba' => 40000,
            'status' => 'Lunas',
        ]);

        // 4. Transaksi BON Bulan Lalu yang Dilunasi Tunai di Bulan Berjalan (75.000)
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Bon September Lunas Oktober',
            'no_transaksi' => 'PJ-SEP-002',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->subMonth(),
            'tanggal_lunas' => now(),
            'pelunas_user_id' => $superadmin->id,
            'metode_pembayaran' => 'Cash',
            'total_modal' => 40000,
            'total_jual' => 75000,
            'grand_total' => 75000,
            'total_laba' => 35000,
            'status' => 'Lunas',
        ]);

        // 5. Transaksi BON Bulan Lalu yang Masih Belum Lunas (200.000 -> Tidak boleh masuk cash)
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Masih Bon',
            'no_transaksi' => 'PJ-SEP-003',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->subMonth(),
            'tanggal_lunas' => null,
            'metode_pembayaran' => 'Bon',
            'total_modal' => 100000,
            'total_jual' => 200000,
            'grand_total' => 200000,
            'total_laba' => 100000,
            'status' => 'Bon',
        ]);

        // 6. Transaksi Cash Bulan Lalu yang Selesai di Bulan Lalu (300.000 -> Tidak boleh masuk cash bulan ini)
        TransaksiFullModel::create([
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Cash September',
            'no_transaksi' => 'PJ-SEP-004',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->subMonth(),
            'tanggal_lunas' => now()->subMonth(),
            'metode_pembayaran' => 'Cash',
            'total_modal' => 150000,
            'total_jual' => 300000,
            'grand_total' => 300000,
            'total_laba' => 150000,
            'status' => 'Lunas',
        ]);

        // Expected Cash = Initial + Modal Awal (500.000) + Cash Okt (100.000) + Pelunasan Bon Okt (75.000) - Kas Keluar (50.000) = Initial + 625.000
        Livewire::test(TransaksiFull::class)
            ->assertViewHas('totalUangCash', function ($totalUangCash) use ($initialCash) {
                return (float) $totalUangCash === ($initialCash + 625000.0);
            });
    }
}
