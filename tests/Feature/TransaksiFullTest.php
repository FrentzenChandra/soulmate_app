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
            'user_id' => $superadmin->id,
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
            'user_id' => $superadmin->id,
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
            ->assertSet('startDate', now()->toDateString())
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

    public function test_filter_ppn_and_type_updates_metrics_dynamically()
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
            'user_id' => $superadmin->id,
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
            'user_id' => $superadmin->id,
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

        // Test Filter PPN
        Livewire::test(TransaksiFull::class)
            ->set('ppnFilter', 'ppn')
            ->assertViewHas('totalPendapatan', 111000.0)
            ->assertViewHas('totalModal', 70000.0)
            ->assertViewHas('totalLaba', 30000.0)
            // Test Filter Non-PPN
            ->set('ppnFilter', 'non_ppn')
            ->assertViewHas('totalPendapatan', 50000.0)
            ->assertViewHas('totalModal', 20000.0)
            ->assertViewHas('totalLaba', 30000.0)
            // Test Filter Servis saja
            ->set('ppnFilter', 'semua')
            ->set('tipeFilter', 'servis')
            ->assertViewHas('totalPendapatan', 50000.0);
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
}
