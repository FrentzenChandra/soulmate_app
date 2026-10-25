<?php

namespace Tests\Feature\Menu;

use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class KasirPosTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected MasterPpn $ppnZero;
    protected MasterPpn $ppn12;
    protected Produk $produk1;
    protected Produk $produk2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();

        $this->cabang1 = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'alamat' => 'Jl. Jendral Sudirman No. 1',
            'no_wa' => '081234567891',
            'status' => 'aktif',
        ]);

        $this->cabang2 = Cabang::create([
            'kode_cabang' => 'CB-SGL',
            'nama_cabang' => 'Cabang Sungailiat',
            'alamat' => 'Jl. Sudirman Sungailiat',
            'no_wa' => '081234567892',
            'status' => 'aktif',
        ]);

        $this->ppnZero = MasterPpn::create([
            'nama' => 'Non PPN (0%)',
            'persen' => 0,
            'is_aktif' => true,
        ]);

        $this->ppn12 = MasterPpn::create([
            'nama' => 'PPN 12%',
            'persen' => 12,
            'is_aktif' => true,
        ]);

        $this->produk1 = Produk::create([
            'nama_produk' => 'Mouse Logitech B100',
            'barcode' => '899123456001',
            'harga_beli' => 45000,
            'harga_jual' => 65000,
            'status' => 'aktif',
        ]);

        $this->produk2 = Produk::create([
            'nama_produk' => 'Keyboard Mechanical RGB',
            'barcode' => '899123456002',
            'harga_beli' => 250000,
            'harga_jual' => 350000,
            'status' => 'aktif',
        ]);

        Stok::create([
            'cabang_id' => $this->cabang1->id,
            'produk_id' => $this->produk1->id,
            'jumlah' => 20,
            'min_stok' => 5,
            'is_aktif' => true,
        ]);

        Stok::create([
            'cabang_id' => $this->cabang1->id,
            'produk_id' => $this->produk2->id,
            'jumlah' => 10,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);
    }

    public static function authorizedRolesProvider(): array
    {
        return [
            'Role Owner' => ['Owner'],
            'Role Superadmin' => ['Superadmin'],
            'Role Admin' => ['Admin'],
            'Role Bendahara' => ['Bendahara'],
            'Role Operasional' => ['Operasional'],
            'Role Magang' => ['Magang'],
        ];
    }

    public static function unauthorizedRolesProvider(): array
    {
        return [
            'Role Teknisi' => ['Teknisi'],
        ];
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('kasir.produk'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('authorizedRolesProvider')]
    public function test_authorized_roles_can_access_kasir_pos(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('kasir.produk'));
        $response->assertOk();
    }

    #[DataProvider('unauthorizedRolesProvider')]
    public function test_unauthorized_roles_receive_403_forbidden(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('kasir.produk'));
        $response->assertForbidden();
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('kasir.produk.view', [], $this->cabang1);

        $response = $this->get(route('kasir.produk'));
        $response->assertOk();
    }

    public function test_user_with_multi_role_inherits_access(): void
    {
        // Teknisi dilarang, tapi jika punya role tambahan Admin maka diizinkan
        $this->actingAsUserWithRoles(['Teknisi', 'Admin'], [], $this->cabang1);

        $response = $this->get(route('kasir.produk'));
        $response->assertOk();
    }

    public function test_invoice_route_cross_branch_security(): void
    {
        $adminCabang1 = $this->actingAsRole('Admin', [], $this->cabang1);

        $penjualanCabang1 = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00001',
            'cabang_id' => $this->cabang1->id,
            'kasir_id' => $adminCabang1->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 65000,
            'total' => 65000,
            'jumlah_bayar' => 65000,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        $penjualanCabang2 = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBSGL-261025-00001',
            'cabang_id' => $this->cabang2->id,
            'kasir_id' => $adminCabang1->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 350000,
            'total' => 350000,
            'jumlah_bayar' => 350000,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        // 1. Admin Cabang 1 BISA akses invoice cabangnya sendiri
        $this->actingAs($adminCabang1);
        $this->get(route('kasir.produk.invoice', $penjualanCabang1->id))
            ->assertOk();

        // 2. Admin Cabang 1 DILARANG (403) akses invoice cabang 2
        $this->get(route('kasir.produk.invoice', $penjualanCabang2->id))
            ->assertForbidden();

        // 3. Owner BISA akses invoice cabang manapun (Global bypass)
        $this->actingAsRole('Owner');
        $this->get(route('kasir.produk.invoice', $penjualanCabang2->id))
            ->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CHECKOUT, CART & INVENTORY MUTATION
    // =========================================================================

    public function test_cart_calculation_subtotal_discount_and_ppn(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(KasirProdukIndex::class)
            ->call('addToCart', $this->produk1->id) // 65,000
            ->call('addToCart', $this->produk2->id) // 350,000 -> subtotal 415,000
            ->assertSet('subtotal', 415000)
            ->set('diskon', 15000) // after diskon 400,000
            ->set('selectedPpnId', $this->ppn12->id) // 12% dari 400,000 = 48,000
            ->assertSet('ppnNominal', 48000)
            ->assertSet('grandTotal', 448000);
    }

    public function test_cash_checkout_reduces_stock_and_creates_sales_record(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $initialStock = Stok::where('cabang_id', $this->cabang1->id)
            ->where('produk_id', $this->produk1->id)
            ->value('jumlah');

        Livewire::test(KasirProdukIndex::class)
            ->call('addToCart', $this->produk1->id)
            ->call('openPaymentModal')
            ->set('jumlahBayar', 70000)
            ->set('metodeBayar', 'Tunai')
            ->call('prosesPembayaran')
            ->assertHasNoErrors();

        // Cek data penjualan tersimpan
        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabang1->id,
            'kasir_id' => $admin->id,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        // Cek stok berkurang 1
        $updatedStock = Stok::where('cabang_id', $this->cabang1->id)
            ->where('produk_id', $this->produk1->id)
            ->value('jumlah');

        $this->assertEquals($initialStock - 1, $updatedStock);
    }

    public function test_simpan_sebagai_bon_requires_customer_selection(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);

        // 1. Tanpa memilih customer -> Gagal simpan BON
        Livewire::test(KasirProdukIndex::class)
            ->call('addToCart', $this->produk1->id)
            ->call('simpanSebagaiBon')
            ->assertDispatched('swal:warning');

        $this->assertDatabaseMissing('penjualans', [
            'status' => 'bon',
        ]);

        // 2. Dengan memilih customer -> Berhasil simpan BON
        $customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'Pelanggan Setia PT',
            'no_hp' => '081299887766',
        ]);

        Livewire::test(KasirProdukIndex::class)
            ->call('addToCart', $this->produk1->id)
            ->call('selectCustomer', $customer->id)
            ->call('simpanSebagaiBon')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'status' => 'bon',
            'metode_bayar' => 'bon',
        ]);
    }

    public function test_quick_customer_registration_in_pos(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(KasirProdukIndex::class)
            ->call('openNewCustomerModal')
            ->set('newCustomerNama', 'Bapak Andi Kasir')
            ->set('newCustomerHp', '081234432112')
            ->call('saveNewCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'cabang_id' => $this->cabang1->id,
            'nama' => 'Bapak Andi Kasir',
            'no_hp' => '081234432112',
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_kasir_pos_menu_appropriately(): void
    {
        // 1. Role Admin -> Menu Kasir POS tampil
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('kasir.produk'));

        // 2. Role Teknisi -> Menu Kasir POS TIDAK tampil
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('kasir.produk'));
    }
}

