<?php

namespace Tests\Feature;

use App\Livewire\Components\NotificationBell;
use App\Livewire\Master\Produk\Index;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductAndStockTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $superadmin;

    protected Cabang $cabangPkp;

    protected Cabang $cabangSgl;

    protected KategoriProduk $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);

        $this->cabangPkp = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'status' => 'aktif',
        ]);

        $this->cabangSgl = Cabang::create([
            'kode_cabang' => 'CB-SGL',
            'nama_cabang' => 'Cabang Sungailiat',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'username' => 'owner',
            'email' => 'owner@soulmate.test',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);
        $this->owner->syncRoles([$ownerRole]);

        $this->superadmin = User::create([
            'name' => 'Admin PKP',
            'username' => 'admin_pkp',
            'email' => 'admin.pkp@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabangPkp->id,
            'status' => 'aktif',
        ]);
        $this->superadmin->syncRoles([$superadminRole]);

        $this->kategori = KategoriProduk::create([
            'nama_kategori' => 'Sparepart Laptop',
            'slug' => 'sparepart-laptop',
        ]);
    }

    public function test_owner_and_superadmin_can_access_product_catalog()
    {
        $this->actingAs($this->owner);
        $response = $this->get('/master/produk');
        $response->assertStatus(200);

        $this->actingAs($this->superadmin);
        $responseSuper = $this->get('/master/produk');
        $responseSuper->assertStatus(200);
    }

    public function test_owner_can_create_new_product_and_initialize_stock()
    {
        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->set('nama_produk', 'SSD NVMe 1TB Samsung 980')
            ->set('kategori_id', $this->kategori->id)
            ->set('barcode', '89999990001')
            ->set('harga_beli', 800000)
            ->set('harga_jual', 1150000)
            ->set('ready_marketplace', true)
            ->set('initialStock', [
                $this->cabangPkp->id => 10,
                $this->cabangSgl->id => 5,
            ])
            ->call('saveProduk')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('produks', [
            'nama_produk' => 'SSD NVMe 1TB Samsung 980',
            'barcode' => '89999990001',
            'harga_beli' => 800000,
            'harga_jual' => 1150000,
        ]);

        $produk = Produk::where('barcode', '89999990001')->first();
        $this->assertNotNull($produk);

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 10,
        ]);

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabangSgl->id,
            'jumlah' => 5,
        ]);
    }

    public function test_owner_can_create_and_edit_categories()
    {
        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->call('openKategoriModal')
            ->set('new_kategori_name', 'Storage & Flashdisk')
            ->call('saveNewKategori')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_produks', [
            'nama_kategori' => 'Storage & Flashdisk',
        ]);
    }

    public function test_branch_stock_adjustment_updates_quantity_correctly()
    {
        $produk = Produk::create([
            'nama_produk' => 'Thermal Paste MX-4',
            'barcode' => '8997770001',
            'harga_beli' => 50000,
            'harga_jual' => 85000,
        ]);

        Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 5,
        ]);

        $this->actingAs($this->superadmin);

        Livewire::test(\App\Livewire\Cabang\Stok\Index::class)
            ->call('openAdjustModal', $produk->id)
            ->set('newStock', 12)
            ->set('catatan', 'Kiriman suplai tambahan dari distributor')
            ->call('saveAdjustment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 12,
        ]);
    }

    public function test_owner_in_hq_can_delete_product_with_soft_delete()
    {
        $produk = Produk::create([
            'nama_produk' => 'Mouse Wireless Logitech M330',
            'barcode' => '899111222',
            'harga_beli' => 150000,
            'harga_jual' => 200000,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->call('deleteProduk', $produk->id);

        $this->assertSoftDeleted('produks', [
            'id' => $produk->id,
        ]);
    }

    public function test_superadmin_cannot_delete_product()
    {
        $produk = Produk::create([
            'nama_produk' => 'Keyboard Mechanical RK61',
            'barcode' => '899333444',
            'harga_beli' => 450000,
            'harga_jual' => 600000,
        ]);

        $this->actingAs($this->superadmin);

        Livewire::test(Index::class)
            ->call('deleteProduk', $produk->id);

        $this->assertDatabaseHas('produks', [
            'id' => $produk->id,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_and_superadmin_can_access_master_kategori()
    {
        $this->actingAs($this->owner);
        $this->get('/master/kategori')->assertStatus(200);

        $this->actingAs($this->superadmin);
        $this->get('/master/kategori')->assertStatus(200);
    }

    public function test_create_edit_and_delete_kategori_in_master_kategori()
    {
        $this->actingAs($this->superadmin);

        // 1. Create Kategori
        Livewire::test(\App\Livewire\Master\Kategori\Index::class)
            ->call('openCreateModal')
            ->set('nama_kategori', 'Aksesoris Gaming')
            ->set('slug', 'aksesoris-gaming')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_produks', [
            'nama_kategori' => 'Aksesoris Gaming',
            'slug' => 'aksesoris-gaming',
        ]);

        $kategori = KategoriProduk::where('slug', 'aksesoris-gaming')->first();

        // 2. Edit Kategori
        Livewire::test(\App\Livewire\Master\Kategori\Index::class)
            ->call('openEditModal', $kategori->id)
            ->set('nama_kategori', 'Aksesoris Gaming & RGB')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_produks', [
            'id' => $kategori->id,
            'nama_kategori' => 'Aksesoris Gaming & RGB',
        ]);

        // 3. Delete Kategori (tanpa produk)
        Livewire::test(\App\Livewire\Master\Kategori\Index::class)
            ->call('deleteKategori', $kategori->id);

        $this->assertDatabaseMissing('kategori_produks', [
            'id' => $kategori->id,
        ]);
    }

    public function test_cannot_delete_kategori_if_has_products()
    {
        $kategori = KategoriProduk::create([
            'nama_kategori' => 'RAM Komputer',
            'slug' => 'ram-komputer',
        ]);

        Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'RAM DDR4 8GB 3200MHz',
            'barcode' => '89998887771',
            'harga_beli' => 200000,
            'harga_jual' => 280000,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(\App\Livewire\Master\Kategori\Index::class)
            ->call('deleteKategori', $kategori->id);

        // Kategori tidak boleh terhapus
        $this->assertDatabaseHas('kategori_produks', [
            'id' => $kategori->id,
        ]);
    }

    public function test_can_sort_and_search_products()
    {
        $this->actingAs($this->owner);

        Produk::create([
            'nama_produk' => 'Alpha Keyboard',
            'barcode' => '11111111',
            'harga_beli' => 100000,
            'harga_jual' => 150000,
        ]);

        Produk::create([
            'nama_produk' => 'Zeta Mouse',
            'barcode' => '22222222',
            'harga_beli' => 50000,
            'harga_jual' => 80000,
        ]);

        // Search test
        Livewire::test(Index::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Keyboard')
            ->assertDontSee('Zeta Mouse');

        // Sorting test
        Livewire::test(Index::class)
            ->call('sortBy', 'nama_produk')
            ->assertSet('sortDirection', 'desc')
            ->call('sortBy', 'harga_jual')
            ->assertSet('sortField', 'harga_jual')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_infinite_scroll_load_more_products_and_categories()
    {
        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->assertSet('perPage', 15)
            ->call('loadMore')
            ->assertSet('perPage', 30);

        Livewire::test(\App\Livewire\Master\Kategori\Index::class)
            ->assertSet('perPage', 15)
            ->call('loadMore')
            ->assertSet('perPage', 30);
    }

    public function test_branch_scoped_notifications_only_alert_active_products_at_threshold()
    {
        // 1. Produk PKP: Stok 1 (kritis, min_stok 3)
        $prodPkp = Produk::create([
            'nama_produk' => 'RAM DDR4 8GB PKP',
            'barcode' => '899111222333',
            'harga_beli' => 200000,
            'harga_jual' => 300000,
        ]);
        Stok::create([
            'produk_id' => $prodPkp->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 1,
            'min_stok' => 3,
            'is_aktif' => true,
        ]);

        // 2. Produk SGL: Stok 10 (aman)
        $prodSgl = Produk::create([
            'nama_produk' => 'SSD 512GB SGL',
            'barcode' => '899444555666',
            'harga_beli' => 400000,
            'harga_jual' => 550000,
        ]);
        Stok::create([
            'produk_id' => $prodSgl->id,
            'cabang_id' => $this->cabangSgl->id,
            'jumlah' => 10,
            'min_stok' => 3,
            'is_aktif' => true,
        ]);

        // Superadmin PKP login: harus melihat 1 critical alert (RAM DDR4 8GB PKP), bukan SSD SGL
        $this->actingAs($this->superadmin);
        Livewire::test(NotificationBell::class)
            ->assertSet('criticalCount', 1)
            ->assertSet('emptyCount', 0)
            ->assertSee('RAM DDR4 8GB PKP');
    }
}
