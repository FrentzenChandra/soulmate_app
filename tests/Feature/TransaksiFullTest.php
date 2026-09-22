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
            'status' => 'Belum Lunas',
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
}
