<?php

namespace Tests\Feature;

use App\Livewire\Laporan\Penjualan\Index as LaporanPenjualanIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $adminBranchA;
    protected User $adminBranchB;
    protected Cabang $cabangA;
    protected Cabang $cabangB;
    protected Customer $customer;
    protected Produk $produk1;
    protected MasterPpn $ppn11;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner']);
        Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Kasir']);

        $this->cabangA = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'status' => 'aktif',
        ]);

        $this->cabangB = Cabang::create([
            'kode_cabang' => 'CB-SKB',
            'nama_cabang' => 'Cabang Sungailiat',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Soulmate',
            'username' => 'owner',
            'email' => 'owner@soulmate.test',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->adminBranchA = User::create([
            'name' => 'Admin Pangkalpinang',
            'username' => 'adminpkp',
            'email' => 'adminpkp@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabangA->id,
            'status' => 'aktif',
        ]);
        $this->adminBranchA->assignRole('Admin');

        $this->adminBranchB = User::create([
            'name' => 'Admin Sungailiat',
            'username' => 'adminskb',
            'email' => 'adminskb@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabangB->id,
            'status' => 'aktif',
        ]);
        $this->adminBranchB->assignRole('Admin');

        $this->customer = Customer::create([
            'cabang_id' => $this->cabangA->id,
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);

        $this->produk1 = Produk::create([
            'kode_produk' => 'PRD-001',
            'barcode' => '899123456',
            'nama_produk' => 'Mouse Wireless Logitech M331',
            'harga_beli' => 100000,
            'harga_jual' => 150000,
            'status' => 'aktif',
        ]);

        $this->ppn11 = MasterPpn::create([
            'nama' => 'PPN 11%',
            'persen' => 11,
            'is_aktif' => true,
        ]);
    }

    public function test_guests_cannot_access_laporan_penjualan(): void
    {
        $response = $this->get(route('laporan.penjualan'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_users_can_access_laporan_penjualan(): void
    {
        $this->actingAs($this->adminBranchA);
        $response = $this->get(route('laporan.penjualan'));
        $response->assertOk();
        $response->assertSee('Riwayat Penjualan');
        $response->assertSee('Soulmate Komputer');
    }

    public function test_metrics_calculation_with_real_and_dummy_transactions(): void
    {
        // Create 1 Real Transaction
        $realSale = Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00001',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 150000,
            'diskon' => 10000,
            'ppn_id' => $this->ppn11->id,
            'ppn_persen' => 11,
            'ppn_nominal' => 15400,
            'total' => 155400,
            'jumlah_bayar' => 200000,
            'kembalian' => 44600,
            'metode_bayar' => 'tunai',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $realSale->id,
            'tipe_item' => 'produk',
            'produk_id' => $this->produk1->id,
            'nama_item' => $this->produk1->nama_produk,
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
        ]);

        // Create 1 Dummy / Formalitas Transaction
        Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00002',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 300000,
            'diskon' => 0,
            'ppn_nominal' => 0,
            'total' => 300000,
            'jumlah_bayar' => 300000,
            'kembalian' => 0,
            'metode_bayar' => 'transfer',
            'status' => 'lunas',
            'is_real' => false,
        ]);

        $this->actingAs($this->adminBranchA);

        Livewire::test(LaporanPenjualanIndex::class)
            ->set('periodeFilter', 'hari_ini')
            ->assertSee('155.400')
            ->assertSee('10.000')
            ->assertSee('15.400')
            ->assertSee('PJ-260915-00001')
            ->assertSee('PJ-260915-00002')
            ->assertSee('Non-Real');
    }

    public function test_loose_multi_word_search(): void
    {
        $sale = Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00010',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 150000,
            'diskon' => 0,
            'ppn_nominal' => 0,
            'total' => 150000,
            'jumlah_bayar' => 150000,
            'kembalian' => 0,
            'metode_bayar' => 'tunai',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $sale->id,
            'tipe_item' => 'produk',
            'produk_id' => $this->produk1->id,
            'nama_item' => $this->produk1->nama_produk,
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
        ]);

        $this->actingAs($this->adminBranchA);

        Livewire::test(LaporanPenjualanIndex::class)
            ->set('search', 'Budi Logitech')
            ->assertSee('PJ-260915-00010')
            ->set('search', 'TidakAdaDiDatabase')
            ->assertDontSee('PJ-260915-00010');
    }

    public function test_branch_isolation_for_non_owner(): void
    {
        Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00001',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 100000,
            'diskon' => 0,
            'ppn_nominal' => 0,
            'total' => 100000,
            'jumlah_bayar' => 100000,
            'kembalian' => 0,
            'metode_bayar' => 'tunai',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        Penjualan::create([
            'cabang_id' => $this->cabangB->id,
            'kasir_id' => $this->adminBranchB->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-SKB-260915-00002',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 200000,
            'diskon' => 0,
            'ppn_nominal' => 0,
            'total' => 200000,
            'jumlah_bayar' => 200000,
            'kembalian' => 0,
            'metode_bayar' => 'tunai',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        $this->actingAs($this->adminBranchA);
        Livewire::test(LaporanPenjualanIndex::class)
            ->assertSee('PJ-260915-00001')
            ->assertDontSee('PJ-260915-00002');

        $this->actingAs($this->owner);
        Livewire::test(LaporanPenjualanIndex::class)
            ->assertSee('INV-PJ-CB-PKP-260915-00001')
            ->assertSee('INV-PJ-CB-SKB-260915-00002')
            ->set('cabangFilter', (string) $this->cabangB->id)
            ->assertDontSee('INV-PJ-CB-PKP-260915-00001')
            ->assertSee('INV-PJ-CB-SKB-260915-00002');
    }

    public function test_detail_modal_interaction(): void
    {
        $sale = Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00099',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 150000,
            'diskon' => 0,
            'ppn_nominal' => 0,
            'total' => 150000,
            'jumlah_bayar' => 150000,
            'kembalian' => 0,
            'metode_bayar' => 'qris',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $sale->id,
            'tipe_item' => 'produk',
            'produk_id' => $this->produk1->id,
            'nama_item' => $this->produk1->nama_produk,
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
        ]);

        $this->actingAs($this->adminBranchA);

        Livewire::test(LaporanPenjualanIndex::class)
            ->assertSet('modalDetailOpen', false)
            ->call('openDetailModal', $sale->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSet('selectedPenjualanId', $sale->id)
            ->call('closeDetailModal')
            ->assertSet('modalDetailOpen', false)
            ->assertSet('selectedPenjualan', null);
    }

    public function test_filter_by_tipe_transaksi_real_and_non_real(): void
    {
        // Real Sale
        Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00101',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 100000,
            'total' => 100000,
            'is_real' => true,
            'status' => 'lunas',
        ]);

        // Non-Real Sale
        Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00102',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 200000,
            'total' => 200000,
            'is_real' => false,
            'status' => 'lunas',
        ]);

        $this->actingAs($this->adminBranchA);

        Livewire::test(LaporanPenjualanIndex::class)
            ->set('tipeTransaksiFilter', 'real')
            ->assertSee('PJ-260915-00101')
            ->assertDontSee('PJ-260915-00102')
            ->set('tipeTransaksiFilter', 'non_real')
            ->assertDontSee('PJ-260915-00101')
            ->assertSee('PJ-260915-00102');
    }

    public function test_update_status_direct_and_filter_status(): void
    {
        $sale = Penjualan::create([
            'cabang_id' => $this->cabangA->id,
            'kasir_id' => $this->adminBranchA->id,
            'customer_id' => $this->customer->id,
            'no_transaksi' => 'INV-PJ-CB-PKP-260915-00201',
            'tipe_transaksi' => 'langsung',
            'tanggal' => Carbon::now(),
            'subtotal' => 100000,
            'total' => 100000,
            'jumlah_bayar' => 0,
            'metode_bayar' => 'bon',
            'is_real' => true,
            'status' => 'bon',
        ]);

        $this->actingAs($this->adminBranchA);

        Livewire::test(LaporanPenjualanIndex::class)
            ->set('statusFilter', 'bon')
            ->assertSee('PJ-260915-00201')
            ->call('updateStatusDirect', $sale->id, 'lunas');

        $this->assertEquals('lunas', $sale->fresh()->status);
        $this->assertEquals(100000, (float) $sale->fresh()->jumlah_bayar);
        $this->assertEquals('tunai', $sale->fresh()->metode_bayar);
    }
}