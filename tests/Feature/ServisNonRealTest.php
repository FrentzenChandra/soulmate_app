<?php

namespace Tests\Feature;

use App\Livewire\Servis\Batal\Index as ServisBatalIndex;
use App\Livewire\Servis\Keluar\Index as ServisKeluarIndex;
use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KategoriProduk;
use App\Models\MasterServis;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\Stok;
use App\Models\User;
use Carbon\Carbon;
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

    public function test_can_create_real_servis_and_sparepart_decrements_stock(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ServisMasukIndex::class)
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('kategoriPerangkat', 'laptop')
            ->set('tipeUnit', 'Asus TUF Gaming A15')
            ->set('keluhan', 'Ganti SSD')
            ->set('isReal', true)
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $servis = ServisMasuk::latest('id')->first();
        $this->assertNotNull($servis);
        $this->assertTrue($servis->is_real);

        // Tambah sparepart ke servis real -> stok harus berkurang
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('selectProdukItem', $this->produk->id, $this->produk->nama_produk, 500000, 10, 30)
            ->set('itemQty', 2)
            ->call('tambahItemTindakan')
            ->assertHasNoErrors();

        $this->stok->refresh();
        $this->assertEquals(8, $this->stok->jumlah);
    }

    public function test_can_create_non_real_servis_with_custom_date_and_sparepart_does_not_decrement_stock(): void
    {
        $this->actingAs($this->user);

        $customDate = '2025-01-15T10:30';

        Livewire::test(ServisMasukIndex::class)
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('kategoriPerangkat', 'laptop')
            ->set('tipeUnit', 'Lenovo ThinkPad T480')
            ->set('keluhan', 'Instal Ulang OS Dummy')
            ->set('isReal', false)
            ->set('customTanggalMasuk', $customDate)
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $servis = ServisMasuk::latest('id')->first();
        $this->assertNotNull($servis);
        $this->assertFalse($servis->is_real);
        $this->assertEquals('2025-01-15 10:30:00', $servis->tanggal_masuk->format('Y-m-d H:i:s'));

        // Tambah sparepart ke servis non-real -> stok TIDAK BOLEH berkurang
        $initialStock = $this->stok->jumlah;
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('selectProdukItem', $this->produk->id, $this->produk->nama_produk, 500000, 10, 30)
            ->set('itemQty', 3)
            ->call('tambahItemTindakan')
            ->assertHasNoErrors();

        $this->stok->refresh();
        $this->assertEquals($initialStock, $this->stok->jumlah);

        // Hapus item tindakan pada servis non-real -> stok TIDAK bertambah
        $detail = ServisDetail::where('servis_id', $servis->id)->first();
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('hapusItemTindakan', $detail->id);

        $this->stok->refresh();
        $this->assertEquals($initialStock, $this->stok->jumlah);
    }

    public function test_filter_tipe_transaksi_works_on_servis_masuk(): void
    {
        $this->actingAs($this->user);

        // Create 1 real and 1 non-real
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Real Unit 1',
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
            'tipe_unit' => 'Non-Real Unit 2',
            'keluhan' => 'Keluhan Non-Real',
            'status' => 'pengecekan',
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        // Filter: semua
        Livewire::test(ServisMasukIndex::class)
            ->set('tipeFilter', 'semua')
            ->assertSee('Real Unit 1')
            ->assertSee('Non-Real Unit 2');

        // Filter: real
        Livewire::test(ServisMasukIndex::class)
            ->set('tipeFilter', 'real')
            ->assertSee('Real Unit 1')
            ->assertDontSee('Non-Real Unit 2');

        // Filter: non_real
        Livewire::test(ServisMasukIndex::class)
            ->set('tipeFilter', 'non_real')
            ->assertDontSee('Real Unit 1')
            ->assertSee('Non-Real Unit 2');
    }

    public function test_filter_tipe_transaksi_works_on_servis_keluar_and_batal(): void
    {
        $this->actingAs($this->user);

        // Servis Keluar
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00003',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Keluar Real Unit',
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
            'tipe_unit' => 'Keluar Non-Real Unit',
            'keluhan' => 'Keluhan',
            'status' => 'selesai',
            'is_cetak_nota' => true,
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->set('tipeFilter', 'real')
            ->assertSee('Keluar Real Unit')
            ->assertDontSee('Keluar Non-Real Unit');

        Livewire::test(ServisKeluarIndex::class)
            ->set('tipeFilter', 'non_real')
            ->assertDontSee('Keluar Real Unit')
            ->assertSee('Keluar Non-Real Unit');

        // Servis Batal
        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260926-00005',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Batal Real Unit',
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
            'tipe_unit' => 'Batal Non-Real Unit',
            'keluhan' => 'Keluhan',
            'status' => 'batal',
            'is_real' => false,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->set('tipeFilter', 'real')
            ->assertSee('Batal Real Unit')
            ->assertDontSee('Batal Non-Real Unit');

        Livewire::test(ServisBatalIndex::class)
            ->set('tipeFilter', 'non_real')
            ->assertDontSee('Batal Real Unit')
            ->assertSee('Batal Non-Real Unit');
    }
}
