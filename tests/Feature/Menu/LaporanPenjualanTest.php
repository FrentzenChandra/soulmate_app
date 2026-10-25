<?php

namespace Tests\Feature\Menu;

use App\Livewire\Laporan\Penjualan\Index as LaporanPenjualanIndex;
use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Stok;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected Produk $produk1;

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

        $this->produk1 = Produk::create([
            'nama_produk' => 'SSD Samsung 500GB',
            'barcode' => '899988877701',
            'harga_beli' => 600000,
            'harga_jual' => 750000,
            'status' => 'aktif',
        ]);

        Stok::create([
            'cabang_id' => $this->cabang1->id,
            'produk_id' => $this->produk1->id,
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
        ];
    }

    public static function unauthorizedRolesProvider(): array
    {
        return [
            'Role Teknisi' => ['Teknisi'],
            'Role Operasional' => ['Operasional'],
            'Role Magang' => ['Magang'],
        ];
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('laporan.penjualan'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('authorizedRolesProvider')]
    public function test_authorized_roles_can_access_laporan_penjualan(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('laporan.penjualan'));
        $response->assertOk();
    }

    #[DataProvider('unauthorizedRolesProvider')]
    public function test_unauthorized_roles_receive_403_forbidden(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('laporan.penjualan'));
        $response->assertForbidden();
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('laporan.penjualan.view', [], $this->cabang1);

        $response = $this->get(route('laporan.penjualan'));
        $response->assertOk();
    }

    public function test_user_with_multi_role_inherits_access(): void
    {
        // Teknisi dilarang, tapi jika digabung dengan Bendahara maka diizinkan
        $this->actingAsUserWithRoles(['Teknisi', 'Bendahara'], [], $this->cabang1);

        $response = $this->get(route('laporan.penjualan'));
        $response->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL, FILTER & RETUR STOK
    // =========================================================================

    public function test_laporan_penjualan_cross_branch_scoping(): void
    {
        $adminCabang1 = $this->actingAsRole('Admin', [], $this->cabang1);

        // Penjualan Cabang 1
        $tx1 = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00010',
            'cabang_id' => $this->cabang1->id,
            'kasir_id' => $adminCabang1->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 750000,
            'total' => 750000,
            'jumlah_bayar' => 750000,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        // Penjualan Cabang 2
        $tx2 = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBSGL-261025-00020',
            'cabang_id' => $this->cabang2->id,
            'kasir_id' => $adminCabang1->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 1500000,
            'total' => 1500000,
            'jumlah_bayar' => 1500000,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        // Admin Cabang 1 hanya melihat transaksi cabangnya
        $this->actingAs($adminCabang1);
        Livewire::test(LaporanPenjualanIndex::class)
            ->assertSee($tx1->no_transaksi_frontend)
            ->assertDontSee($tx2->no_transaksi_frontend);

        // Owner dapat melihat kedua cabang
        $this->actingAsRole('Owner');
        Livewire::test(LaporanPenjualanIndex::class)
            ->assertSee($tx1->no_transaksi_frontend)
            ->assertSee($tx2->no_transaksi_frontend);
    }

    public function test_detail_modal_shows_accurate_items(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $penjualan = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00099',
            'cabang_id' => $this->cabang1->id,
            'kasir_id' => $admin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 750000,
            'total' => 750000,
            'jumlah_bayar' => 750000,
            'status' => 'selesai',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'produk_id' => $this->produk1->id,
            'tipe_item' => 'produk',
            'nama_item' => 'SSD Samsung 500GB',
            'harga' => 750000,
            'qty' => 1,
            'subtotal' => 750000,
        ]);

        Livewire::test(LaporanPenjualanIndex::class)
            ->call('openDetailModal', $penjualan->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('SSD Samsung 500GB');
    }

    public function test_direct_status_update_for_bon_settlement(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $bon = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261025-00088',
            'cabang_id' => $this->cabang1->id,
            'kasir_id' => $admin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 750000,
            'total' => 750000,
            'jumlah_bayar' => 0,
            'status' => 'bon',
            'metode_bayar' => 'bon',
            'is_real' => true,
        ]);

        Livewire::test(LaporanPenjualanIndex::class)
            ->call('updateStatusDirect', $bon->id, 'lunas');

        $this->assertDatabaseHas('penjualans', [
            'id' => $bon->id,
            'status' => 'lunas',
            'jumlah_bayar' => 750000,
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_laporan_penjualan_menu_appropriately(): void
    {
        // 1. Role Admin -> Menu Laporan Penjualan tampil
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('laporan.penjualan'));

        // 2. Role Teknisi -> Menu Laporan Penjualan TIDAK tampil
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('laporan.penjualan'));
    }
}
