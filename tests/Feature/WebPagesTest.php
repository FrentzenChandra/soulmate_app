<?php

namespace Tests\Feature;

use App\Livewire\Web\Home;
use App\Livewire\Web\Produk;
use App\Livewire\Web\Servis;
use App\Livewire\Web\TentangKami;
use App\Livewire\Web\Kontak;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KategoriProduk;
use App\Models\Produk as ProdukModel;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionMatrixSeeder::class);
    }

    public function test_can_access_beranda_page(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('SOULMATE');
        $response->assertSee('KOMPUTER');
        $response->assertSee('TOKO KOMPUTER TERPERCAYA DI PANGKALPINANG');
        $response->assertSee('Kunjungi Toko Kami');
        $response->assertSee('KAMI TERSEDIA DI');
        $response->assertSee('PLATFORM RESMI');
        $response->assertSee('https://katalog.inaproc.id/sm-pamen-sejahtera');
        $response->assertSee('https://siplah.blibli.com/merchant-detail/SSKO-0102');
        $response->assertSee('https://siplah.tokoladang.co.id/m/soulmate-komputer.19533');
        $response->assertSee('https://siplahtelkom.com/store/211481-soulmate-komputer');
        $response->assertSee('https://shopee.co.id/enoharyono24');
    }

    public function test_can_access_produk_page(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'CB-TEST',
            'nama_cabang' => 'Cabang Test',
            'status' => 'aktif',
        ]);

        $kategori = KategoriProduk::create([
            'nama_kategori' => 'Laptop & Notebook',
            'cabang_id' => $cabang->id,
        ]);

        $produk = ProdukModel::create([
            'kode_produk' => 'PRD-001',
            'nama_produk' => 'Asus Vivobook 14',
            'kategori_id' => $kategori->id,
            'harga_jual' => 8500000,
            'stok' => 5,
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $response = $this->get(route('web.produk'));
        $response->assertOk();
        $response->assertSee('Semua Produk');
        $response->assertSee('Temukan berbagai pilihan perangkat komputer, laptop, dan aksesoris');

        Livewire::test(Produk::class)
            ->set('search', 'Strix')
            ->assertSee('ASUS ROG Strix G16')
            ->assertSee('19.999.000');
    }

    public function test_produk_filters_are_active(): void
    {
        // 1. Filter Kategori
        Livewire::test(Produk::class)
            ->call('setKategori', 'printer')
            ->assertSet('kategori', 'printer')
            ->assertSee('Epson L3250')
            ->assertSee('Canon G3020')
            ->assertDontSee('ASUS ROG Strix G16');

        // 2. Pagination (80 items per page)
        Livewire::test(Produk::class)
            ->assertSet('perPage', 80)
            ->assertSee('Acer Aspire 5 A514-56')
            ->assertSee('Epson L3250');

        // 3. Verify Filter Harga and Brand are removed from /produk, and Tentang Kami and Kontak removed from nav
        $response = $this->get(route('web.produk'));
        $response->assertOk();
        $response->assertDontSee('Filter Harga');
        $response->assertDontSee('Filter Batas Harga Maksimal');
        $response->assertDontSee('>Tentang Kami<', false);
        $response->assertDontSee('>Kontak<', false);
    }

    public function test_cart_system_and_whatsapp_order(): void
    {
        $component = Livewire::test(Produk::class)
            // Add product 1 (Acer Aspire 5 - Rp 8.499.000)
            ->call('addToCart', 1)
            ->assertSet('cart.1.qty', 1)
            ->assertSee('Produk "Acer Aspire 5 A514-56" berhasil ditambahkan ke keranjang!')
            // Increment
            ->call('incrementQty', 1)
            ->assertSet('cart.1.qty', 2)
            // Decrement
            ->call('decrementQty', 1)
            ->assertSet('cart.1.qty', 1)
            // WhatsApp Order
            ->set('customerName', 'Triwanto')
            ->set('customerPhone', '08123456789')
            ->set('customerNotes', 'Pangkalpinang')
            ->call('orderViaWhatsApp')
            ->assertRedirect();

        // Clear cart
        $component->call('clearCart')
            ->assertSet('cart', []);
    }

    public function test_can_access_servis_tracking_page(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'CB-TEST',
            'nama_cabang' => 'Cabang Test',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['cabang_id' => $cabang->id]);

        $customer = Customer::create([
            'nama' => 'John Doe',
            'no_hp' => '081299998888',
            'cabang_id' => $cabang->id,
        ]);

        $servis = ServisMasuk::create([
            'cabang_id' => $cabang->id,
            'created_by' => $user->id,
            'customer_id' => $customer->id,
            'no_servis' => 'INV-SV-TEST-261011-00001',
            'tanggal_masuk' => now(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad X270',
            'serial_number' => 'SN123456',
            'keluhan' => 'Layar bergaris',
            'status' => 'pengecekan',
            'is_real' => true,
        ]);

        $response = $this->get(route('web.servis'));
        $response->assertOk();
        $response->assertSee('Cek Status Servis');
        // Ensure mock reference card is not shown by default
        $response->assertDontSee('S0020510');
        $response->assertDontSee('PT Prima Timah Utama');

        // Test search by raw backend invoice
        Livewire::test(Servis::class)
            ->set('kodeInput', 'INV-SV-TEST-261011-00001')
            ->call('cekStatus')
            ->assertSee('Lenovo ThinkPad X270')
            ->assertSee('Pengecekan')
            ->assertSee('John Doe');

        // Test search by frontend formatted code SV-261011-00001
        Livewire::test(Servis::class)
            ->set('kodeInput', 'SV-261011-00001')
            ->call('cekStatus')
            ->assertSee('Lenovo ThinkPad X270')
            ->assertSee('John Doe');

        // Test search by serial number
        Livewire::test(Servis::class)
            ->set('kodeInput', 'SN123456')
            ->call('cekStatus')
            ->assertSee('Lenovo ThinkPad X270')
            ->assertSee('John Doe');

        // Phone number search must NOT be permitted (only service code)
        Livewire::test(Servis::class)
            ->set('kodeInput', '081299998888')
            ->call('cekStatus')
            ->assertSee('tidak ditemukan')
            ->assertDontSee('Lenovo ThinkPad X270');

        // Test not found state
        Livewire::test(Servis::class)
            ->set('kodeInput', 'TIDAK-ADA-123')
            ->call('cekStatus')
            ->assertSee('tidak ditemukan')
            ->assertDontSee('Lenovo ThinkPad X270');

        // Test Jemput Servisan mode and WhatsApp submission
        Livewire::test(Servis::class)
            ->assertSee('Jemput Servisan')
            ->assertSee('Ajukan Jemput Servisan')
            ->call('openJemputMode')
            ->assertSet('activeMode', 'jemput')
            ->assertSee('Formulir Jemput Servisan')
            ->assertSee('Proses Jemput Servisan')
            ->call('incrementJumlah')
            ->assertSet('jemputJumlah', 2)
            ->call('decrementJumlah')
            ->assertSet('jemputJumlah', 1)
            ->set('jemputNama', 'Andi Wijaya')
            ->set('jemputWhatsapp', '081234567890')
            ->set('jemputPerangkat', 'Laptop Asus ROG & Printer Epson')
            ->set('jemputAlamat', 'Jl. Jendral Sudirman No. 10, Pangkalpinang')
            ->set('jemputKeluhan', 'Laptop mati total, printer macet')
            ->set('jemputJadwalTipe', 'segera')
            ->call('kirimJemputKeWA')
            ->assertRedirectContains('https://wa.me/6281995466255?text=');
    }

    public function test_can_access_tentang_kami_page(): void
    {
        $response = $this->get(route('web.tentang-kami'));
        $response->assertOk();
        $response->assertSee('SOULMATE KOMPUTER');
        $response->assertSee('Solusi Teknologi untuk Kebutuhan Anda');
        $response->assertSee('Perjalanan Kami');
        $response->assertSee('Visi, Misi, dan Nilai Kami');
        $response->assertSee('Suasana Toko Kami');
    }

    public function test_can_access_kontak_page_and_send_message(): void
    {
        $response = $this->get(route('web.kontak'));
        $response->assertOk();
        $response->assertSee('Hubungi Kami');
        $response->assertSee('0819 9546 6255');
        $response->assertSee('soulmatekomputer24@gmail.com');
        $response->assertSee('Jam Operasional');

        Livewire::test(Kontak::class)
            ->set('nama', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->set('no_wa', '08123456789')
            ->set('subjek', 'Servis')
            ->set('pesan', 'Halo saya ingin tanya biaya servis laptop.')
            ->call('kirimPesan')
            ->assertSet('terkirim', true)
            ->assertSee('Pesan Berhasil Terkirim!');
    }
}
