<?php

namespace Tests\Feature\Menu;

use App\Livewire\Transaksi\Full as TransaksiFullComponent;
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

class TransaksiFullTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Customer $customer;
    protected MetodePembayaran $metodeCash;
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
            'nama' => 'PT Bangka Sejahtera',
            'no_hp' => '081122334455',
        ]);

        $this->metodeCash = MetodePembayaran::create([
            'nama' => 'Cash',
            'is_aktif' => true,
            'urutan' => 1,
        ]);

        $this->produk1 = Produk::create([
            'cabang_id' => $this->cabang1->id,
            'nama_produk' => 'SSD NVMe Samsung 980 1TB',
            'barcode' => 'NVME-SAM-1TB',
            'harga_beli' => 1100000,
            'harga_jual' => 1450000,
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (EKSKLUSIF SUPERADMIN & OWNER)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('transaksi.full'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Admin is forbidden' => ['Admin', 403],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_transaksi_full_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('transaksi.full'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('transaksi.full.view', [], $this->cabang1);

        $this->get(route('transaksi.full'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('transaksi.full'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD TRANSAKSI FULL (DENGAN LABA & MARGIN)
    // =========================================================================

    public function test_superadmin_can_create_new_transaksi_full(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(TransaksiFullComponent::class)
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
                    'nama_item' => 'SSD NVMe Samsung 980 1TB',
                    'qty' => 1,
                    'harga_modal' => 1100000,
                    'harga_jual' => 1450000,
                    'subtotal_modal' => 1100000,
                    'subtotal_jual' => 1450000,
                    'laba' => 350000,
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
            'nama_pelanggan' => 'PT Bangka Sejahtera',
            'metode_pembayaran' => 'Cash',
            'status' => 'Lunas',
            'total_modal' => 1100000,
            'total_jual' => 1450000,
            'total_laba' => 350000,
            'grand_total' => 1450000,
        ]);
    }

    public function test_detail_modal_shows_laba_and_margin_for_superadmin(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $tx = TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00060',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $superadmin->id,
            'customer_id' => $this->customer->id,
            'nama_pelanggan' => 'PT Bangka Sejahtera',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'total_modal' => 1100000,
            'total_jual' => 1450000,
            'total_laba' => 350000,
            'grand_total' => 1450000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        TransaksiFullDetail::create([
            'transaksi_full_id' => $tx->id,
            'nama_item' => 'SSD NVMe Samsung 980 1TB',
            'qty' => 1,
            'harga_modal' => 1100000,
            'harga_jual' => 1450000,
            'subtotal_modal' => 1100000,
            'subtotal_jual' => 1450000,
            'laba' => 350000,
        ]);

        Livewire::test(TransaksiFullComponent::class)
            ->call('openDetailModal', $tx->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('PT Bangka Sejahtera')
            ->assertSee('SSD NVMe Samsung 980 1TB')
            ->assertSee('Laba');
    }

    public function test_superadmin_can_delete_transaksi_full(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $tx = TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00061',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Umum',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => now(),
            'total_modal' => 50000,
            'total_jual' => 75000,
            'total_laba' => 25000,
            'grand_total' => 75000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        Livewire::test(TransaksiFullComponent::class)
            ->call('confirmDelete', $tx->id, $tx->no_transaksi)
            ->assertSet('modalDeleteOpen', true)
            ->call('deleteTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('transaksi_fulls', [
            'id' => $tx->id,
        ]);
    }

    // =========================================================================
    // TAHAP 2C: PDF EXPORT SECURITY
    // =========================================================================

    public function test_pdf_export_allowed_for_superadmin_but_forbidden_for_admin(): void
    {
        // 1. Superadmin DIIZINKAN export PDF
        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('transaksi.full.export-pdf'))
            ->assertOk();

        // 2. Admin DILARANG (403)
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('transaksi.full.export-pdf'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_transaksi_full_for_superadmin_and_owner(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('transaksi.full'));

        $this->actingAsRole('Owner');
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('transaksi.full'));
    }

    public function test_sidebar_does_not_display_transaksi_full_for_admin(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('transaksi.full'));
    }
}

