<?php

namespace Tests\Feature\Menu;

use App\Livewire\Transaksi\Harian as TransaksiHarian;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\TransaksiFull;
use App\Models\TransaksiFullDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class TransaksiHarianTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Customer $customer;
    protected MetodePembayaran $metodeCash;
    protected MasterPpn $ppn11;
    protected Produk $produk1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Cabang Pangkalpinang',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Merdeka No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'Budi Retail',
            'no_hp' => '081299887711',
        ]);

        $this->metodeCash = MetodePembayaran::create([
            'nama' => 'Cash',
            'is_aktif' => true,
            'urutan' => 1,
        ]);

        $this->ppn11 = MasterPpn::create([
            'nama' => 'PPN 11%',
            'persen' => 11,
            'is_aktif' => true,
        ]);

        $this->produk1 = Produk::create([
            'cabang_id' => $this->cabang1->id,
            'nama_produk' => 'Mouse Wireless Logitech M170',
            'barcode' => 'LOGI-M170',
            'harga_beli' => 120000,
            'harga_jual' => 165000,
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (RULE 22 ZERO LABA STANDARD)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transaksi.harian'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin is excluded from Transaksi Harian (Rule 22)' => ['Superadmin', 403],
            'Admin has access' => ['Admin', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
            'Owner has access via bypass' => ['Owner', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_transaksi_harian_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('transaksi.harian'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('transaksi.harian.view', [], $this->cabang1);

        $this->get(route('transaksi.harian'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('transaksi.harian'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD TRANSAKSI HARIAN
    // =========================================================================

    public function test_admin_can_create_new_transaksi_harian(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(TransaksiHarian::class)
            ->call('openCreateModal')
            ->assertSet('modalFormOpen', true)
            ->set('formTipeTransaksi', 'penjualan')
            ->set('formCustomerType', 'langganan')
            ->set('formCustomerId', $this->customer->id)
            ->set('formNamaPelanggan', $this->customer->nama)
            ->set('formMetodePembayaranId', $this->metodeCash->id)
            ->set('formMetodePembayaran', $this->metodeCash->nama)
            ->set('formStatus', 'Lunas')
            ->set('items', [
                [
                    'produk_id' => $this->produk1->id,
                    'nama_item' => 'Mouse Wireless Logitech M170',
                    'qty' => 2,
                    'harga_modal' => 120000,
                    'harga_jual' => 165000,
                    'subtotal_modal' => 240000,
                    'subtotal_jual' => 330000,
                    'laba' => 90000,
                    'ppn_id' => null,
                    'ppn_persen' => 0,
                    'ppn_nominal' => 0,
                ],
            ])
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'nama_pelanggan' => 'Budi Retail',
            'metode_pembayaran' => 'Cash',
            'status' => 'Lunas',
            'grand_total' => 330000,
        ]);

        $this->assertDatabaseHas('transaksi_full_details', [
            'nama_item' => 'Mouse Wireless Logitech M170',
            'qty' => 2,
            'harga_jual' => 165000,
            'subtotal_jual' => 330000,
        ]);
    }

    public function test_admin_can_update_status_bon_to_lunas(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $tx = TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00050',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'customer_id' => $this->customer->id,
            'nama_pelanggan' => 'Budi Retail',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'total_modal' => 120000,
            'total_jual' => 165000,
            'grand_total' => 165000,
            'total_laba' => 45000,
            'status' => 'Bon',
            'metode_pembayaran' => 'Cash',
        ]);

        Livewire::test(TransaksiHarian::class)
            ->call('updateStatusDirect', $tx->id, 'Lunas')
            ->assertHasNoErrors();

        $this->assertEquals('Lunas', $tx->fresh()->status);
        $this->assertNotNull($tx->fresh()->tanggal_lunas);
    }

    public function test_detail_modal_shows_transaction_details_without_laba(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $tx = TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00051',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'customer_id' => $this->customer->id,
            'nama_pelanggan' => 'Budi Retail',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'total_modal' => 200000,
            'total_jual' => 280000,
            'grand_total' => 280000,
            'total_laba' => 80000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        TransaksiFullDetail::create([
            'transaksi_full_id' => $tx->id,
            'nama_item' => 'Keyboard Gaming RGB',
            'qty' => 1,
            'harga_modal' => 200000,
            'harga_jual' => 280000,
            'subtotal_modal' => 200000,
            'subtotal_jual' => 280000,
            'laba' => 80000,
        ]);

        Livewire::test(TransaksiHarian::class)
            ->call('openDetailModal', $tx->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('Budi Retail')
            ->assertSee('Keyboard Gaming RGB')
            ->assertDontSee('Laba Bersih');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_transaksi_harian_for_admin_and_bendahara(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('transaksi.harian'));

        $this->actingAsRole('Bendahara', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('transaksi.harian'));
    }

    public function test_sidebar_does_not_display_transaksi_harian_for_superadmin_and_teknisi(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('transaksi.harian'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('transaksi.harian'));
    }
}

