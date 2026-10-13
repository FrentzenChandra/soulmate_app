<?php

namespace Tests\Feature;

use App\Livewire\Kasir\Produk\Index;
use App\Models\Cabang;
use App\Models\MasterPpn;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KasirPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $superadmin;

    protected User $kasir;

    protected Cabang $cabangPkp;

    protected MasterPpn $ppn11;

    protected MasterPpn $ppn0;

    protected Produk $produk1;

    protected Produk $produk2;

    protected Produk $produkZero;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir']);

        $viewPerm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'kasir.produk.view']);
        $kasirRole->givePermissionTo($viewPerm);
        $superadminRole->givePermissionTo($viewPerm);

        $this->cabangPkp = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
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

        $this->kasir = User::create([
            'name' => 'Kasir PKP',
            'username' => 'kasir_pkp',
            'email' => 'kasir.pkp@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabangPkp->id,
            'status' => 'aktif',
        ]);
        $this->kasir->syncRoles([$kasirRole]);

        $this->ppn0 = MasterPpn::create([
            'nama' => 'Tanpa PPN',
            'persen' => 0,
            'is_aktif' => true,
        ]);

        $this->ppn11 = MasterPpn::create([
            'nama' => 'PPN 11%',
            'persen' => 11,
            'is_aktif' => true,
        ]);

        $this->produk1 = Produk::create([
            'nama_produk' => 'Mouse Wireless Logitech M330',
            'barcode' => '899111222',
            'harga_beli' => 120000,
            'harga_jual' => 175000,
        ]);

        Stok::create([
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 10,
        ]);

        $this->produk2 = Produk::create([
            'nama_produk' => 'Flashdisk Sandisk 64GB',
            'barcode' => '899333444',
            'harga_beli' => 60000,
            'harga_jual' => 95000,
        ]);

        Stok::create([
            'produk_id' => $this->produk2->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 5,
        ]);

        $this->produkZero = Produk::create([
            'nama_produk' => 'Keyboard Gaming RGB (Kosong)',
            'barcode' => '899555666',
            'harga_beli' => 300000,
            'harga_jual' => 450000,
        ]);

        Stok::create([
            'produk_id' => $this->produkZero->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 0,
        ]);
    }

    public function test_kasir_superadmin_and_owner_can_access_pos_module()
    {
        $this->actingAs($this->kasir);
        $this->get('/kasir/produk')->assertStatus(200);

        $this->actingAs($this->superadmin);
        $this->get('/kasir/produk')->assertStatus(200);

        $this->actingAs($this->owner);
        $this->get('/kasir/produk')->assertStatus(200);
    }

    public function test_can_add_products_to_cart_and_validate_subtotal()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('addToCart', $this->produk2->id)
            ->assertSet('cart.'.$this->produk1->id.'.qty', 1)
            ->assertSet('cart.'.$this->produk2->id.'.qty', 1)
            ->call('adjustQty', $this->produk1->id, 1) // jadi 2 pcs
            ->assertSet('cart.'.$this->produk1->id.'.qty', 2)
            ->assertSee('Rp '.number_format((175000 * 2) + 95000, 0, ',', '.'));
    }

    public function test_cannot_add_product_with_zero_stock()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produkZero->id)
            ->assertSet('cart', [])
            ->assertDispatched('swal:warning');
    }

    public function test_cart_quantity_cannot_exceed_available_branch_stock()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produk2->id) // stok max = 5
            ->call('updateQty', $this->produk2->id, 10) // coba isi 10
            ->assertSet('cart.'.$this->produk2->id.'.qty', 5) // harus dipaksa ke max 5
            ->assertDispatched('swal:warning');
    }

    public function test_financial_calculations_with_discount_and_ppn()
    {
        $this->actingAs($this->kasir);

        // Produk 1: 175.000 * 2 = 350.000
        // Diskon: 50.000 -> Base = 300.000
        // PPN 11% = 33.000 -> Grand Total = 333.000
        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('adjustQty', $this->produk1->id, 1)
            ->set('diskon', 50000)
            ->set('selectedPpnId', $this->ppn11->id)
            ->assertSet('subtotal', 350000)
            ->assertSet('ppnNominal', 33000)
            ->assertSet('grandTotal', 333000);
    }

    public function test_can_create_new_customer_and_attach_to_sale()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('openNewCustomerModal')
            ->set('newCustomerNama', 'Hendra Wijaya')
            ->set('newCustomerHp', '081234567890')
            ->call('saveNewCustomer')
            ->assertSet('customerType', 'langganan');

        $this->assertDatabaseHas('customers', [
            'cabang_id' => $this->cabangPkp->id,
            'nama' => 'Hendra Wijaya',
            'no_hp' => '081234567890',
            'tipe' => 'langganan',
        ]);
    }

    public function test_successful_cash_checkout_decrements_stock_and_creates_invoice()
    {
        $this->actingAs($this->kasir);

        // Produk 1: 2 unit @ 175.000 = 350.000
        // Stok awal = 10, sisa harus 8
        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('adjustQty', $this->produk1->id, 1)
            ->set('selectedPpnId', $this->ppn0->id) // Tanpa PPN -> Grand Total = 350.000
            ->call('openPaymentModal')
            ->set('metodeBayar', 'cash')
            ->set('jumlahBayar', 400000) // Bayar 400.000 -> Kembalian 50.000
            ->set('catatan', 'Nota atas nama Toko Berkah')
            ->call('prosesPembayaran')
            ->assertSet('modalBayarOpen', false)
            ->assertDispatched('open-print-invoice')
            ->assertSet('cart', []);

        // Verifikasi database Penjualan
        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'kasir_id' => $this->kasir->id,
            'tipe_transaksi' => 'penjualan_barang',
            'total' => 350000,
            'jumlah_bayar' => 400000,
            'kembalian' => 50000,
            'is_real' => true,
            'status' => 'selesai',
        ]);

        $penjualan = Penjualan::where('cabang_id', $this->cabangPkp->id)->first();
        $this->assertNotNull($penjualan);
        $this->assertStringStartsWith('INV-PJ-CBPKP-', $penjualan->no_transaksi);
        $this->assertMatchesRegularExpression('/^PJ-\d{6}-\d{5}$/', $penjualan->no_transaksi_frontend);

        // Verifikasi detail penjualan
        $this->assertDatabaseHas('penjualan_details', [
            'penjualan_id' => $penjualan->id,
            'produk_id' => $this->produk1->id,
            'qty' => 2,
            'subtotal' => 350000,
        ]);

        // Verifikasi stok cabang berkurang dari 10 menjadi 8
        $this->assertDatabaseHas('stoks', [
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 8,
        ]);
    }

    public function test_search_barcode_and_product_name_on_enter()
    {
        $this->actingAs($this->kasir);

        // 1. Search unique product name on enter
        Livewire::test(Index::class)
            ->set('search', 'Flashdisk')
            ->call('handleSearchOrScan')
            ->assertSet('search', '')
            ->assertCount('cart', 1);

        // 2. Scan exact barcode via dedicated scanner modal
        Livewire::test(Index::class)
            ->call('openScanModal')
            ->set('scanBarcode', '899111222')
            ->call('processScanBarcode')
            ->assertSet('scanBarcode', '')
            ->assertCount('cart', 1);
    }

    public function test_penjualan_saves_ppn_persen_and_ppn_nominal_correctly()
    {
        $this->actingAs($this->kasir);

        // Subtotal: 175.000, Diskon: 25.000 -> DPP = 150.000
        // PPN 11% = 16.500 -> Total = 166.500
        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->set('diskon', 25000)
            ->set('selectedPpnId', $this->ppn11->id)
            ->call('openPaymentModal')
            ->set('metodeBayar', 'cash')
            ->set('jumlahBayar', 200000)
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        $penjualan = Penjualan::latest('id')->first();
        $this->assertNotNull($penjualan);
        $this->assertEquals(175000, (float) $penjualan->subtotal);
        $this->assertEquals(25000, (float) $penjualan->diskon);
        $this->assertEquals(11.00, (float) $penjualan->ppn_persen);
        $this->assertEquals(16500.00, (float) $penjualan->ppn_nominal);
        $this->assertEquals(166500.00, (float) $penjualan->total);
        $this->assertEquals(200000, (float) $penjualan->jumlah_bayar);
        $this->assertEquals(33500.00, (float) $penjualan->kembalian);
    }

    public function test_infinite_scroll_load_more_increments_per_page()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->assertSet('perPage', 12)
            ->call('loadMore')
            ->assertSet('perPage', 24)
            ->set('search', 'Mouse')
            ->assertSet('perPage', 12);
    }

    public function test_case_insensitive_and_multi_word_search()
    {
        $this->actingAs($this->kasir);

        // Search with UPPERCASE
        Livewire::test(Index::class)
            ->set('search', 'LOGITECH')
            ->call('handleSearchOrScan')
            ->assertSet('search', '')
            ->assertCount('cart', 1);

        // Search with mixed case and multiple words
        Livewire::test(Index::class)
            ->set('search', 'fLasHdIsk 64gb')
            ->call('handleSearchOrScan')
            ->assertSet('search', '')
            ->assertCount('cart', 1);
    }

    public function test_dedicated_barcode_scanner_modal_processes_items()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('openScanModal')
            ->assertSet('modalScanOpen', true)
            ->set('scanBarcode', '899111222')
            ->call('processScanBarcode')
            ->assertSet('scanBarcode', '')
            ->assertSet('lastScannedName', 'Mouse Wireless Logitech M330')
            ->assertCount('cart', 1)
            ->set('scanBarcode', '899333444')
            ->call('processScanBarcode')
            ->assertCount('cart', 2)
            ->call('closeScanModal')
            ->assertSet('modalScanOpen', false);
    }

    public function test_can_register_new_customer()
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('openNewCustomerModal')
            ->set('newCustomerNama', 'Budi Santoso Langganan')
            ->set('newCustomerHp', '081999888777')
            ->call('saveNewCustomer')
            ->assertSet('customerType', 'langganan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'cabang_id' => $this->cabangPkp->id,
            'nama' => 'Budi Santoso Langganan',
            'no_hp' => '081999888777',
            'tipe' => 'langganan',
        ]);
    }

    public function test_non_real_transaction_does_not_decrement_stock_and_sets_is_real_false()
    {
        $this->actingAs($this->kasir);

        // Produk 1 stok awal = 10
        // Lakukan transaksi Non-Real (isReal = false) sebanyak 4 unit
        Livewire::test(Index::class)
            ->call('toggleRealMode')
            ->assertSet('isReal', false)
            ->call('addToCart', $this->produk1->id)
            ->call('updateQty', $this->produk1->id, 4)
            ->set('selectedPpnId', $this->ppn0->id)
            ->call('openPaymentModal')
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        // Verifikasi penjualan tersimpan dengan is_real = false
        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'total' => 700000,
            'is_real' => false,
            'status' => 'selesai',
        ]);

        // Verifikasi STOK TIDAK BERKURANG (tetap 10)
        $this->assertDatabaseHas('stoks', [
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 10,
        ]);
    }

    public function test_kasir_pos_only_displays_products_active_in_branch()
    {
        $this->actingAs($this->kasir);

        $cabangSlt = Cabang::create([
            'kode_cabang' => 'CB-SLT',
            'nama_cabang' => 'Cabang Sungailiat',
            'status' => 'aktif',
        ]);

        // Buat produk baru yang HANYA aktif di Cabang Sungailiat (tidak aktif di PKP)
        $inactiveProduct = Produk::create([
            'nama_produk' => 'VGA RTX 4090 Cabang Lain',
            'barcode' => '899888999111',
            'harga_beli' => 25000000,
            'harga_jual' => 30000000,
        ]);

        Stok::create([
            'produk_id' => $inactiveProduct->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 0,
            'min_stok' => 3,
            'is_aktif' => false,
        ]);

        Stok::create([
            'produk_id' => $inactiveProduct->id,
            'cabang_id' => $cabangSlt->id,
            'jumlah' => 5,
            'min_stok' => 1,
            'is_aktif' => true,
        ]);

        // Kasir PKP tidak boleh melihat produk yang tidak aktif di PKP
        Livewire::test(Index::class)
            ->assertViewHas('produks', function ($produks) use ($inactiveProduct) {
                return ! $produks->contains('id', $inactiveProduct->id);
            });
    }

    public function test_cannot_save_bon_without_customer(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->set('customerType', 'umum')
            ->call('simpanSebagaiBon')
            ->assertDispatched('swal:warning');
    }

    public function test_can_save_bon_with_customer_and_decrements_stock(): void
    {
        $this->actingAs($this->kasir);

        $customer = \App\Models\Customer::create([
            'nama' => 'Budi Santoso Bon',
            'no_hp' => '081234567890',
            'cabang_id' => $this->cabangPkp->id,
        ]);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('selectCustomer', $customer->id)
            ->call('simpanSebagaiBon')
            ->assertDispatched('open-print-invoice')
            ->assertSet('cart', []);

        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'customer_id' => $customer->id,
            'status' => 'bon',
            'metode_bayar' => 'bon',
            'jumlah_bayar' => 0,
            'kembalian' => 0,
            'total' => 175000,
            'is_real' => true,
        ]);

        // Stok berkurang dari 10 menjadi 9
        $this->assertDatabaseHas('stoks', [
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabangPkp->id,
            'jumlah' => 9,
        ]);
    }

    public function test_kembalian_calculation_with_default_tunai_and_discount_update(): void
    {
        $this->actingAs($this->kasir);

        // Subtotal = 175.000
        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('openPaymentModal')
            ->assertSet('metodeBayar', 'Tunai')
            ->assertSet('grandTotal', 175000)
            ->set('jumlahBayar', 200000)
            ->assertSet('kembalian', 25000)
            // Kasir menambahkan diskon 25.000 saat modal pembayaran terbuka
            ->set('diskon', 25000)
            ->assertSet('grandTotal', 150000)
            ->set('jumlahBayar', 200000)
            ->assertSet('kembalian', 50000)
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'subtotal' => 175000,
            'diskon' => 25000,
            'total' => 150000,
            'jumlah_bayar' => 200000,
            'kembalian' => 50000,
            'metode_bayar' => 'Tunai',
        ]);
    }

    public function test_kembalian_is_zero_for_non_cash_methods(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('addToCart', $this->produk1->id)
            ->call('openPaymentModal')
            ->call('selectMetodeBayar', 'QRIS')
            ->assertSet('metodeBayar', 'QRIS')
            ->assertSet('kembalian', 0)
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'total' => 175000,
            'kembalian' => 0,
            'metode_bayar' => 'QRIS',
        ]);
    }

    public function test_kasir_can_edit_item_subtotal_in_non_real_mode(): void
    {
        $this->actingAs($this->kasir);

        // Produk 1 harga 175.000, ubah ke Non-Real, tambah ke keranjang lalu ubah qty jadi 2 (subtotal awal 350.000)
        // Kasir menawar / negosiasi subtotal menjadi 320.000
        Livewire::test(Index::class)
            ->call('toggleRealMode')
            ->assertSet('isReal', false)
            ->call('addToCart', $this->produk1->id)
            ->call('updateQty', $this->produk1->id, 2)
            ->call('updateItemSubtotal', $this->produk1->id, '320.000')
            ->assertSet('cart.' . $this->produk1->id . '.subtotal', 320000)
            ->assertSet('cart.' . $this->produk1->id . '.harga', 160000)
            ->assertSet('cart.' . $this->produk1->id . '.is_custom_price', true)
            ->assertSet('grandTotal', 320000)
            ->call('openPaymentModal')
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        $this->assertDatabaseHas('penjualans', [
            'cabang_id' => $this->cabangPkp->id,
            'subtotal' => 320000,
            'total' => 320000,
            'is_real' => false,
        ]);

        $this->assertDatabaseHas('penjualan_details', [
            'produk_id' => $this->produk1->id,
            'qty' => 2,
            'harga' => 160000,
            'subtotal' => 320000,
        ]);
    }

    public function test_kasir_can_reset_custom_item_price_to_catalog_price(): void
    {
        $this->actingAs($this->kasir);

        // Produk 1 harga katalog 175.000
        Livewire::test(Index::class)
            ->call('toggleRealMode')
            ->call('addToCart', $this->produk1->id)
            ->call('updateQty', $this->produk1->id, 2)
            ->call('updateItemSubtotal', $this->produk1->id, 320000)
            ->assertSet('cart.' . $this->produk1->id . '.is_custom_price', true)
            ->call('resetItemPrice', $this->produk1->id)
            ->assertSet('cart.' . $this->produk1->id . '.harga', 175000)
            ->assertSet('cart.' . $this->produk1->id . '.subtotal', 350000)
            ->assertSet('cart.' . $this->produk1->id . '.is_custom_price', false)
            ->assertSet('grandTotal', 350000);
    }

    public function test_real_mode_rejects_update_item_subtotal_with_403(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->assertSet('isReal', true)
            ->call('addToCart', $this->produk1->id)
            ->call('updateItemSubtotal', $this->produk1->id, 150000)
            ->assertStatus(403);
    }

    public function test_real_mode_rejects_custom_date_on_checkout_with_403(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->assertSet('isReal', true)
            ->call('addToCart', $this->produk1->id)
            ->set('customTanggal', '2026-05-10T14:30')
            ->call('openPaymentModal')
            ->call('prosesPembayaran')
            ->assertStatus(403);
    }

    public function test_real_mode_rejects_tampered_item_price_on_checkout_with_403(): void
    {
        $this->actingAs($this->kasir);

        // Simulasi manipulasi state keranjang secara paksa via DevTools
        Livewire::test(Index::class)
            ->assertSet('isReal', true)
            ->call('addToCart', $this->produk1->id)
            ->set('cart.' . $this->produk1->id . '.harga', 100000)
            ->call('openPaymentModal')
            ->call('prosesPembayaran')
            ->assertStatus(403);
    }

    public function test_non_real_mode_allows_custom_date_and_saves_to_penjualan(): void
    {
        $this->actingAs($this->kasir);

        $customTime = '2026-04-15 10:30:00';

        Livewire::test(Index::class)
            ->call('toggleRealMode')
            ->assertSet('isReal', false)
            ->call('addToCart', $this->produk1->id)
            ->set('customTanggal', '2026-04-15T10:30')
            ->call('openPaymentModal')
            ->call('prosesPembayaran')
            ->assertDispatched('open-print-invoice');

        $penjualan = Penjualan::where('cabang_id', $this->cabangPkp->id)
            ->where('is_real', false)
            ->first();

        $this->assertNotNull($penjualan);
        $this->assertEquals('2026-04-15 10:30:00', $penjualan->tanggal->format('Y-m-d H:i:s'));
        // Pastikan prefix invoice menggunakan tanggal kustom (260415)
        $this->assertStringContainsString('INV-PJ-CBPKP-260415-', $penjualan->no_transaksi);
    }

    public function test_switching_to_real_mode_resets_custom_date_and_item_prices(): void
    {
        $this->actingAs($this->kasir);

        Livewire::test(Index::class)
            ->call('toggleRealMode') // switch to Non-Real
            ->set('customTanggal', '2026-04-15T10:30')
            ->call('addToCart', $this->produk1->id)
            ->call('updateItemSubtotal', $this->produk1->id, 100000)
            ->assertSet('cart.' . $this->produk1->id . '.is_custom_price', true)
            ->call('toggleRealMode') // switch back to Real
            ->assertSet('isReal', true)
            ->assertSet('customTanggal', null)
            ->assertSet('cart.' . $this->produk1->id . '.harga', 175000)
            ->assertSet('cart.' . $this->produk1->id . '.subtotal', 175000)
            ->assertSet('cart.' . $this->produk1->id . '.is_custom_price', false);
    }
}
