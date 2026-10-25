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

    public function test_admin_can_filter_transaksi_harian_by_date_and_periods()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $component = Livewire::test(TransaksiHarian::class);
        $component->assertSet('startDate', now()->toDateString())
            ->assertSet('endDate', now()->toDateString())
            ->assertSet('periodeMode', 'hari_ini')
            ->call('setPeriodeMode', 'range')
            ->assertSet('periodeMode', 'range')
            ->assertSet('startDate', now()->startOfMonth()->toDateString())
            ->call('setPeriodeMode', 'bulanan')
            ->assertSet('periodeMode', 'bulanan')
            ->call('setPeriodeMode', 'tahunan')
            ->assertSet('periodeMode', 'tahunan')
            ->call('setPeriodeMode', 'semua')
            ->assertSet('periodeMode', 'semua')
            ->assertSet('startDate', '')
            ->assertSet('endDate', '');
    }

    public function test_admin_can_inline_update_metode_transaksi_harian()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $metode = MetodePembayaran::create([
            'nama' => 'QRIS BCA',
            'cabang_id' => $admin->cabang_id,
            'is_aktif' => true,
        ]);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nama_pelanggan' => 'Client Harian Metode',
            'no_transaksi' => 'INV-PJ-CBPKP-260922-00006',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->toDateString(),
            'total_modal' => 10000,
            'total_jual' => 20000,
            'total_laba' => 10000,
            'grand_total' => 20000,
            'status' => 'Lunas',
        ]);

        Livewire::test(TransaksiHarian::class)
            ->call('updateMetodeDirect', $transaksi->id, $metode->id);

        $this->assertEquals($metode->id, $transaksi->fresh()->metode_pembayaran_id);
        $this->assertEquals('QRIS BCA', $transaksi->fresh()->metode_pembayaran);
    }

    public function test_uang_cash_metric_scoped_to_running_month_and_includes_cross_month_bon_settlement_in_transaksi_harian()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        // Ambil baseline cash dari seeder
        $initialCash = 0;
        Livewire::test(TransaksiHarian::class)->assertViewHas('totalUangCash', function ($val) use (&$initialCash) {
            $initialCash = (float) $val;
            return true;
        });

        // 1. Modal awal bulan berjalan (500.000)
        \App\Models\ModalAwal::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nominal' => 500000,
            'tanggal' => now(),
            'catatan' => 'Modal kasir hari ini',
        ]);

        // 2. Kas keluar cash bulan berjalan (50.000)
        \App\Models\KasKeluar::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nominal' => 50000,
            'tanggal' => now(),
            'keterangan' => 'Beli ATK Cash',
        ]);

        // 3. Transaksi Cash Lunas Bulan Berjalan (100.000)
        TransaksiFullModel::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
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
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nama_pelanggan' => 'Pelanggan Bon September Lunas Oktober',
            'no_transaksi' => 'PJ-SEP-002',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now()->subMonth(),
            'tanggal_lunas' => now(),
            'pelunas_user_id' => $admin->id,
            'metode_pembayaran' => 'Cash',
            'total_modal' => 40000,
            'total_jual' => 75000,
            'grand_total' => 75000,
            'total_laba' => 35000,
            'status' => 'Lunas',
        ]);

        // Expected Cash = Initial + Modal Awal (500.000) + Cash Okt (100.000) + Pelunasan Bon Okt (75.000) - Kas Keluar (50.000) = Initial + 625.000
        Livewire::test(TransaksiHarian::class)
            ->assertSee('Uang Cash')
            ->assertViewHas('totalUangCash', function ($totalUangCash) use ($initialCash) {
                return (float) $totalUangCash === ($initialCash + 625000.0);
            });
    }

    public function test_user_without_edit_or_delete_permission_is_blocked_from_mutations()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $admin->roles()->detach();
        // Give only view permission
        $admin->syncPermissions(['transaksi.harian.view']);
        $this->actingAs($admin);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nama_pelanggan' => 'Pelanggan Test Perm',
            'no_transaksi' => 'PJ-PERM-001',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'metode_pembayaran' => 'Cash',
            'total_modal' => 10000,
            'total_jual' => 20000,
            'grand_total' => 20000,
            'status' => 'Lunas',
        ]);

        // Create blocked
        Livewire::test(TransaksiHarian::class)
            ->call('openCreateModal')
            ->assertStatus(403);

        // Edit blocked
        Livewire::test(TransaksiHarian::class)
            ->call('openEditModal', $transaksi->id)
            ->assertStatus(403);

        // Update status blocked
        Livewire::test(TransaksiHarian::class)
            ->call('updateStatusDirect', $transaksi->id, 'Bon')
            ->assertStatus(403);

        // Update metode blocked
        Livewire::test(TransaksiHarian::class)
            ->call('updateMetodeDirect', $transaksi->id, 1)
            ->assertStatus(403);

        // Delete blocked
        Livewire::test(TransaksiHarian::class)
            ->call('confirmDelete', $transaksi->id, 'PJ-PERM-001')
            ->assertStatus(403);

        Livewire::test(TransaksiHarian::class)
            ->set('deleteId', $transaksi->id)
            ->call('deleteTransaksi')
            ->assertStatus(403);
    }

    public function test_user_with_status_permission_can_pelunasan_but_blocked_from_edit_modal()
    {
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'transaksi.harian.status', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'transaksi.harian.view', 'guard_name' => 'web']);

        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $admin->roles()->detach();
        // Give view and status permissions only (no edit permission)
        $admin->syncPermissions(['transaksi.harian.view', 'transaksi.harian.status']);
        $this->actingAs($admin);

        $transaksi = TransaksiFullModel::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'nama_pelanggan' => 'Pelanggan Bon Test',
            'no_transaksi' => 'PJ-BON-001',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'metode_pembayaran' => 'Cash',
            'total_modal' => 10000,
            'total_jual' => 20000,
            'grand_total' => 20000,
            'status' => 'Bon',
        ]);

        // Edit modal must be blocked (403)
        Livewire::test(TransaksiHarian::class)
            ->call('openEditModal', $transaksi->id)
            ->assertStatus(403);

        // Pelunasan / update status direct must SUCCEED
        Livewire::test(TransaksiHarian::class)
            ->call('updateStatusDirect', $transaksi->id, 'Lunas')
            ->assertStatus(200);

        $this->assertEquals('Lunas', $transaksi->fresh()->status);
        $this->assertNotNull($transaksi->fresh()->tanggal_lunas);
    }
}

