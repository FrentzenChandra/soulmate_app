<?php

namespace Tests\Feature;

use App\Livewire\Transaksi\Harian as TransaksiHarian;
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

class TransaksiHarianAndTutupKasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_transaksi_harian()
    {
        $this->get(route('transaksi.harian'))->assertRedirect(route('login'));
    }

    public function test_superadmin_cannot_access_transaksi_harian()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $this->get(route('transaksi.harian'))->assertStatus(403);
    }

    public function test_admin_can_view_transaksi_harian_and_create_transaction()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $this->get(route('transaksi.harian'))->assertStatus(200);

        $produk = Produk::first();
        $stokAwal = Stok::where('produk_id', $produk->id)->where('cabang_id', $admin->cabang_id)->value('jumlah') ?? 50;

        $metode = MetodePembayaran::first();
        $ppn = MasterPpn::first();

        $test = Livewire::test(TransaksiHarian::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Joko Susilo')
            ->set('formNoHp', '0855555555')
            ->set('formTipeTransaksi', 'servis')
            ->set('formStatus', 'Lunas')
            ->set('formMetodePembayaranId', $metode->id)
            ->set('formMetodePembayaran', $metode->nama_metode)
            ->set('formPpnId', $ppn->id)
            ->set('formPpnPersen', $ppn->persentase)
            ->set('items.0.nama_item', 'Ganti LCD Laptop')
            ->set('items.0.qty', 1)
            ->set('items.0.harga_modal', 300000)
            ->set('items.0.harga_jual', 450000)
            ->call('recalculateRow', 0)
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'nama_pelanggan' => 'Joko Susilo',
            'tipe_transaksi' => 'servis',
            'status' => 'Lunas',
        ]);

        // Zero Stock Mutation assertion
        $stokAkhir = Stok::where('produk_id', $produk->id)->where('cabang_id', $admin->cabang_id)->value('jumlah') ?? 50;
        $this->assertEquals($stokAwal, $stokAkhir);
    }

    public function test_admin_can_inline_update_status_transaksi_harian()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nama_pelanggan' => 'Client Harian',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00005',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->toDateString(),
            'total_modal' => 10000,
            'total_jual' => 20000,
            'total_laba' => 10000,
            'grand_total' => 20000,
            'status' => 'Belum Lunas',
        ]);

        Livewire::test(TransaksiHarian::class)
            ->call('updateStatusDirect', $transaksi->id, 'Lunas');

        $this->assertEquals('Lunas', $transaksi->fresh()->status);
    }

    public function test_admin_can_filter_transaksi_harian_by_date()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $component = Livewire::test(TransaksiHarian::class);
        $component->assertSet('filterDate', now()->toDateString())
            ->set('filterDate', '2026-09-01')
            ->assertSet('filterDate', '2026-09-01');
    }
}
