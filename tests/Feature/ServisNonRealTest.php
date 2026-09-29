<?php

namespace Tests\Feature;

use App\Livewire\Servis\Batal\Index as ServisBatalIndex;
use App\Livewire\Servis\Keluar\Index as ServisKeluarIndex;
use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Livewire\Servis\NonReal\Index as ServisNonRealIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServisNonRealTest extends TestCase
{
    use RefreshDatabase;

    protected Cabang $cabang;
    protected User $user;
    protected Customer $customer;
    protected Produk $produk;
    protected Stok $stok;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teknisi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);

        $this->cabang = Cabang::create([
            'nama_cabang' => 'Soulmate Pusat PKP',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Masjid Jamik',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->user = User::create([
            'name' => 'Admin Cabang',
            'username' => 'admin_cabang',
            'email' => 'admin@soulmate.com',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->user->assignRole('Admin');

        $this->customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);

        $kategori = KategoriProduk::create([
            'nama_kategori' => 'Sparepart',
        ]);

        $this->produk = Produk::create([
            'cabang_id' => $this->cabang->id,
            'kategori_id' => $kategori->id,
            'kode_produk' => 'SSD-512',
            'nama_produk' => 'SSD NVMe 512GB',
            'harga_modal' => 350000,
            'harga_jual' => 500000,
            'stok_minimum' => 2,
            'is_aktif' => true,
        ]);

        $this->stok = Stok::create([
            'cabang_id' => $this->cabang->id,
            'produk_id' => $this->produk->id,
            'jumlah' => 10,
            'is_aktif' => true,
        ]);
    }

    public function test_real_servis_pages_only_show_real_servis(): void
    {
        $this->actingAs($this->user);

        // Create 1 real and 1 non-real in masuk
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Real Unit Masuk',
            'keluhan' => 'Keluhan Real',
            'status' => 'pengecekan',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00002',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Non-Real Unit Masuk',
            'keluhan' => 'Keluhan Non-Real',
            'status' => 'pengecekan',
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        // Servis Masuk only shows Real Unit
        Livewire::test(ServisMasukIndex::class)
            ->assertSee('Real Unit Masuk')
            ->assertDontSee('Non-Real Unit Masuk');

        // Servis Keluar
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00003',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Real Unit Keluar',
            'keluhan' => 'Keluhan',
            'status' => 'selesai',
            'is_cetak_nota' => true,
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00004',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Non-Real Unit Keluar',
            'keluhan' => 'Keluhan',
            'status' => 'selesai',
            'is_cetak_nota' => true,
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->assertSee('Real Unit Keluar')
            ->assertDontSee('Non-Real Unit Keluar');

        // Servis Batal
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00005',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Real Unit Batal',
            'keluhan' => 'Keluhan',
            'status' => 'batal',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00006',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Non-Real Unit Batal',
            'keluhan' => 'Keluhan',
            'status' => 'batal',
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->assertSee('Real Unit Batal')
            ->assertDontSee('Non-Real Unit Batal');
    }

    public function test_can_create_and_manage_non_real_servis_without_affecting_stock(): void
    {
        $this->actingAs($this->user);

        // Test creation on ServisNonRealIndex
        Livewire::test(ServisNonRealIndex::class)
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('kategoriPerangkat', 'laptop')
            ->set('tipeUnit', 'Lenovo ThinkPad Non Real')
            ->set('keluhan', 'Ganti Keyboard & Thermal Paste')
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $servis = ServisMasuk::latest('id')->first();
        $this->assertNotNull($servis);
        $this->assertFalse($servis->is_real);
        $this->assertEquals('Lenovo ThinkPad Non Real', $servis->tipe_unit);

        // Check it is visible on ServisNonRealIndex
        Livewire::test(ServisNonRealIndex::class)
            ->assertSee('Lenovo ThinkPad Non Real');

        // Check it is NOT visible on ServisMasukIndex
        Livewire::test(ServisMasukIndex::class)
            ->assertDontSee('Lenovo ThinkPad Non Real');

        // Tambah sparepart ke servis non-real -> stok fisik TIDAK BOLEH berkurang
        $initialStock = $this->stok->jumlah;

        Livewire::test(ServisNonRealIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('setItemType', 'sparepart')
            ->call('selectProdukItem', $this->produk->id, $this->produk->nama_produk, 500000, 10, 30)
            ->set('itemQty', 3)
            ->call('tambahItemTindakan')
            ->assertHasNoErrors();

        $this->stok->refresh();
        $this->assertEquals($initialStock, $this->stok->jumlah);

        // Hapus item tindakan pada servis non-real -> stok TIDAK bertambah
        $detail = ServisDetail::where('servis_id', $servis->id)->first();
        $this->assertNotNull($detail);

        Livewire::test(ServisNonRealIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('hapusItemTindakan', $detail->id)
            ->assertHasNoErrors();

        $this->stok->refresh();
        $this->assertEquals($initialStock, $this->stok->jumlah);
    }

    public function test_can_access_servis_non_real_route(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('servis.non-real'));
        $response->assertStatus(200);
        $response->assertSee('Servis Non Real');
    }
}
