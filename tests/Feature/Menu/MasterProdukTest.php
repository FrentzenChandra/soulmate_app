<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Produk\Index as MasterProdukIndex;
use App\Livewire\Web\Produk as WebProduk;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterProdukTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected KategoriProduk $kategori;

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

        $this->kategori = KategoriProduk::create([
            'nama_kategori' => 'Storage & SSD',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('master.produk'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Magang has access' => ['Magang', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Operasional is forbidden' => ['Operasional', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_master_produk_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('master.produk'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('master.produk.view', [], $this->cabang1);

        $this->get(route('master.produk'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('master.produk'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD MASTER PRODUK
    // =========================================================================

    public function test_superadmin_can_create_new_master_produk(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(MasterProdukIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalProdukOpen', true)
            ->set('nama_produk', 'SSD Kingston NV2 500GB PCIe 4.0')
            ->set('barcode', 'SNV2S-500G')
            ->set('kategori_id', $this->kategori->id)
            ->set('harga_beli', 550000)
            ->set('harga_jual', 675000)
            ->set('initialStock', [$this->cabang1->id => 10])
            ->call('saveProduk')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'SSD Kingston NV2 500GB PCIe 4.0',
            'barcode' => 'SNV2S-500G',
            'harga_beli' => 550000,
            'harga_jual' => 675000,
        ]);
    }

    public function test_superadmin_can_edit_master_produk(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'RAM DDR4 8GB 3200MHz',
            'barcode' => 'RAM-DDR4-8G',
            'harga_beli' => 250000,
            'harga_jual' => 320000,
            'status' => 'aktif',
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openEditModal', $produk->id)
            ->assertSet('modalProdukOpen', true)
            ->set('harga_jual', 340000)
            ->call('saveProduk')
            ->assertHasNoErrors();

        $this->assertEquals(340000, $produk->fresh()->harga_jual);
    }

    public function test_admin_blocked_from_creating_or_deleting_produk(): void
    {
        // Admin hanya berhak view, tidak berhak create atau delete
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Flashdisk Sandisk 64GB',
            'barcode' => 'SD-64G',
            'harga_beli' => 60000,
            'harga_jual' => 85000,
            'status' => 'aktif',
        ]);

        // 1. Ditolak 403 saat openCreateModal
        Livewire::test(MasterProdukIndex::class)
            ->call('openCreateModal')
            ->assertForbidden();

        // 2. Ditolak 403 saat openDeleteConfirmation
        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertForbidden();
    }

    public function test_superadmin_deactivates_product_stock_in_branch(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Pad XL Polos',
            'barcode' => 'MPAD-XL',
            'harga_beli' => 35000,
            'harga_jual' => 55000,
            'status' => 'aktif',
        ]);

        \App\Models\Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 5,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertSet('modalDeleteOpen', true)
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'is_aktif' => 0,
        ]);
    }

    public function test_superadmin_deactivates_product_zeroes_stock_and_hides_marketplace(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $cabang2 = Cabang::create([
            'nama_cabang' => 'Cabang Sungailiat',
            'kode_cabang' => 'CB-SGL',
            'status' => 'aktif',
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Pad XL Polos',
            'barcode' => 'MPAD-XL',
            'harga_beli' => 35000,
            'harga_jual' => 55000,
            'ready_marketplace' => true,
        ]);

        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);
        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $cabang2->id, 'jumlah' => 7, 'min_stok' => 2, 'is_aktif' => true]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 0,
            'is_aktif' => 0,
        ]);

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $cabang2->id,
            'jumlah' => 7,
            'is_aktif' => 1,
        ]);

        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_deactivated_product_is_hidden_from_public_marketplace(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Pad XL Polos',
            'barcode' => 'MPAD-XL',
            'harga_beli' => 35000,
            'harga_jual' => 55000,
            'ready_marketplace' => true,
        ]);

        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);

        Livewire::test(WebProduk::class)->assertSee('Mouse Pad XL Polos');

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->call('confirmDelete');

        Livewire::test(WebProduk::class)->assertDontSee('Mouse Pad XL Polos');
    }

    public function test_deactivating_product_without_stock_row_does_not_error(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Kabel HDMI 3M',
            'barcode' => 'HDMI-3M',
            'harga_beli' => 25000,
            'harga_jual' => 45000,
            'ready_marketplace' => true,
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_delete_modal_shows_hero_banner_when_product_has_photo(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'SSD Kingston NV2 500GB',
            'barcode' => 'SNV2S-500G',
            'harga_beli' => 550000,
            'harga_jual' => 675000,
            'ready_marketplace' => true,
            'foto_url' => 'images/logo_bulat.png',
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertSeeHtml('h-48')
            ->assertSeeHtml('object-contain')
            ->assertSee('Website Marketplace');
    }

    public function test_delete_modal_shows_placeholder_hero_when_product_has_no_photo(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Kabel HDMI 3M',
            'barcode' => 'HDMI-3M',
            'harga_beli' => 25000,
            'harga_jual' => 45000,
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertDontSeeHtml('object-contain')
            ->assertSee('Foto produk belum tersedia');
    }

    public function test_non_hq_staff_can_find_deactivated_product_to_reactivate(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Pad XL Polos',
            'barcode' => 'MPAD-XL',
            'harga_beli' => 35000,
            'harga_jual' => 55000,
            'ready_marketplace' => true,
        ]);

        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->call('confirmDelete');

        Livewire::test(MasterProdukIndex::class)
            ->set('stokFilter', 'non_aktif')
            ->assertSee('Mouse Pad XL Polos');
    }

    public function test_delete_modal_shows_placeholder_hero_when_photo_file_is_missing(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'SSD Kingston NV2 500GB',
            'barcode' => 'SNV2S-500G',
            'harga_beli' => 550000,
            'harga_jual' => 675000,
            'foto_url' => 'images/foto-yang-tidak-ada.png',
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertDontSeeHtml('object-contain')
            ->assertSee('Foto produk belum tersedia');
    }

    public function test_delete_modal_shows_hero_banner_for_uploaded_storage_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/produk-uji.webp', 'x');

        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'SSD Kingston NV2 500GB',
            'barcode' => 'SNV2S-500G',
            'harga_beli' => 550000,
            'harga_jual' => 675000,
            'foto_url' => 'storage/products/produk-uji.webp',
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertSeeHtml('object-contain')
            ->assertDontSee('Foto produk belum tersedia');
    }

    public function test_hq_delete_modal_does_not_claim_stock_will_be_reset(): void
    {
        $this->actingAsRole('Owner');
        session(['active_branch_id' => 'hq']);

        $produk = Produk::create([
            'nama_produk' => 'Kabel HDMI 3M',
            'barcode' => 'HDMI-3M',
            'harga_beli' => 25000,
            'harga_jual' => 45000,
        ]);

        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 120, 'min_stok' => 2, 'is_aktif' => true]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertDontSee('akan menjadi 0')
            ->assertDontSeeHtml('object-contain');
    }

    public function test_deactivate_aborts_when_staff_has_no_branch_context(): void
    {
        $this->actingAsRole('Superadmin', ['cabang_id' => null], $this->cabang1);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Pad XL Polos',
            'barcode' => 'MPAD-XL',
            'harga_beli' => 35000,
            'harga_jual' => 55000,
        ]);

        Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->call('confirmDelete')
            ->assertForbidden();

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 5,
        ]);
    }

    public function test_owner_at_hq_can_soft_delete_produk_permanently(): void
    {
        $this->actingAsRole('Owner');
        session(['active_branch_id' => 'hq']);

        $produk = Produk::create([
            'nama_produk' => 'Kabel HDMI 3M',
            'barcode' => 'HDMI-3M',
            'harga_beli' => 25000,
            'harga_jual' => 45000,
            'status' => 'aktif',
        ]);

        Livewire::test(MasterProdukIndex::class)
            ->call('openDeleteConfirmation', $produk->id)
            ->assertSet('modalDeleteOpen', true)
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('produks', [
            'id' => $produk->id,
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_master_produk_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('master.produk'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('master.produk'));
    }

    public function test_sidebar_does_not_display_master_produk_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('master.produk'));

        $this->actingAsRole('Operasional', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('master.produk'));
    }
}

