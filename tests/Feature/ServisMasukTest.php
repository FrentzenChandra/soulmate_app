<?php

namespace Tests\Feature;

use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServisMasukTest extends TestCase
{
    use RefreshDatabase;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected User $owner;
    protected User $admin;
    protected User $teknisi;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teknisi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Soulmate Pusat PKP',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Masjid Jamik',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->cabang2 = Cabang::create([
            'nama_cabang' => 'Soulmate Cabang Sungailiat',
            'kode_cabang' => 'CB-SGL',
            'alamat' => 'Jl. Sudirman',
            'no_wa' => '081298765432',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'username' => 'owner',
            'email' => 'owner@soulmate.com',
            'password' => bcrypt('password'),
            'cabang_id' => null,
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->admin = User::create([
            'name' => 'Admin PKP',
            'username' => 'adminpkp',
            'email' => 'admin@soulmate.com',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');

        $this->teknisi = User::create([
            'name' => 'Teknisi PKP',
            'username' => 'teknisipkp',
            'email' => 'teknisi@soulmate.com',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
        ]);
        $this->teknisi->assignRole('Teknisi');
    }

    public function test_guests_cannot_access_servis_masuk(): void
    {
        $response = $this->get(route('servis.masuk'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_access_servis_masuk(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('servis.masuk'));
        $response->assertOk();

        $this->actingAs($this->teknisi);
        $response = $this->get(route('servis.masuk'));
        $response->assertOk();

        $this->actingAs($this->owner);
        $response = $this->get(route('servis.masuk'));
        $response->assertOk();
    }

    public function test_can_receive_new_servis_and_auto_create_customer(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ServisMasukIndex::class)
            ->set('customerSearch', 'Andi Pratama')
            ->set('newCustomerNoHp', '081299887766')
            ->set('kategoriPerangkat', 'laptop')
            ->set('tipeUnit', 'Asus TUF Gaming FX505')
            ->set('kelengkapanChips', ['Unit', 'Adaptor / Charger'])
            ->set('customKelengkapan', 'Tas Ransel Asus')
            ->set('keluhan', 'Layar blank hitam setelah booting')
            ->set('kondisiBarang', 'Mulus, baret pemakaian normal')
            ->call('simpanServisMasuk')
            ->assertHasNoErrors()
            ->assertSet('modalSuccessOpen', true);

        // Verifikasi Customer baru dibuat
        $customer = Customer::where('nama', 'Andi Pratama')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('081299887766', $customer->no_hp);

        // Verifikasi Record Servis Masuk
        $servis = ServisMasuk::where('customer_id', $customer->id)->first();
        $this->assertNotNull($servis);
        $this->assertStringContainsString('INV-SV-CBPKP-', $servis->no_servis);
        $this->assertStringContainsString('SV-', $servis->no_servis_frontend);
        $this->assertEquals('pengecekan', $servis->status);
        $this->assertEquals('Asus TUF Gaming FX505', $servis->tipe_unit);
        $this->assertStringContainsString('Adaptor / Charger', $servis->kelengkapan);
        $this->assertStringContainsString('Tas Ransel Asus', $servis->kelengkapan);
        $this->assertNull($servis->teknisi_id);

        // Verifikasi Status Log Awal
        $log = ServisStatusLog::where('servis_id', $servis->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('pengecekan', $log->status);
        $this->assertEquals($this->admin->id, $log->user_id);
    }

    public function test_can_receive_servis_for_existing_customer(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'PT Timah Utama',
            'no_hp' => '081122334455',
        ]);

        Livewire::test(ServisMasukIndex::class)
            ->set('customerSearch', 'PT Timah Utama')
            ->set('selectedCustomerId', $customer->id)
            ->set('kategoriPerangkat', 'printer')
            ->set('tipeUnit', 'Epson L3110')
            ->set('keluhan', 'Hasil print garis putus-putus')
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $servis = ServisMasuk::where('customer_id', $customer->id)->first();
        $this->assertNotNull($servis);
        $this->assertEquals('printer', $servis->kategori_perangkat);
    }

    public function test_branch_isolation_for_non_owner(): void
    {
        $customer1 = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'User PKP', 'no_hp' => '081111']);
        $customer2 = Customer::create(['cabang_id' => $this->cabang2->id, 'nama' => 'User SGL', 'no_hp' => '082222']);

        $servis1 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00001',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer1->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Acer Swift 3 PKP',
            'keluhan' => 'Baterai drop',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        $servis2 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBSGL-260915-00001',
            'cabang_id' => $this->cabang2->id,
            'customer_id' => $customer2->id,
            'created_by' => $this->owner->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Yoga SGL',
            'keluhan' => 'Keyboard macet',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        // Admin PKP hanya melihat servis cabang 1
        $this->actingAs($this->admin);
        Livewire::test(ServisMasukIndex::class)
            ->assertSee('Acer Swift 3 PKP')
            ->assertDontSee('Lenovo Yoga SGL');

        // Owner HQ melihat kedua servis
        $this->actingAs($this->owner);
        session(['active_branch_id' => 'hq']);
        Livewire::test(ServisMasukIndex::class)
            ->assertSee('Acer Swift 3 PKP')
            ->assertSee('Lenovo Yoga SGL');
    }

    public function test_loose_multi_word_search_and_filters(): void
    {
        $this->actingAs($this->admin);

        $cust = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Hendro Wijaya', 'no_hp' => '081987654321']);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00010',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $cust->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'MacBook Pro M1 2020',
            'serial_number' => 'C02F1234MD6R',
            'keluhan' => 'Ganti pasta thermal dan instal ulang OS',
            'status' => 'pengerjaan',
            'tanggal_masuk' => now(),
        ]);

        // Multi-word search
        Livewire::test(ServisMasukIndex::class)
            ->set('search', 'hendro macbook m1')
            ->assertSee('MacBook Pro M1 2020')
            ->set('search', 'tidakcocok123')
            ->assertDontSee('MacBook Pro M1 2020');

        // Filter status
        Livewire::test(ServisMasukIndex::class)
            ->set('statusFilter', 'perbaikan')
            ->assertSee('MacBook Pro M1 2020')
            ->set('statusFilter', 'pengecekan')
            ->assertDontSee('MacBook Pro M1 2020');
    }

    public function test_can_update_status_and_creates_status_log(): void
    {
        $this->actingAs($this->teknisi);

        $cust = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Bambang', 'no_hp' => '08123']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00020',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $cust->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Gaming Ryzen 5',
            'keluhan' => 'Mati total',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(ServisMasukIndex::class)
            ->call('openStatusModal', $servis->id)
            ->set('updateStatus', 'selesai')
            ->set('updateCatatan', 'Power Supply diganti baru 550W Bronze, tes nyala normal.')
            ->call('simpanUpdateStatus')
            ->assertHasNoErrors()
            ->assertSet('modalStatusOpen', false);

        $servis->refresh();
        $this->assertEquals('selesai', $servis->status);
        $this->assertNotNull($servis->tanggal_selesai);

        $lastLog = ServisStatusLog::where('servis_id', $servis->id)->latest('id')->first();
        $this->assertNotNull($lastLog);
        $this->assertEquals('selesai', $lastLog->status);
        $this->assertEquals($this->teknisi->id, $lastLog->user_id);
        $this->assertStringContainsString('Power Supply diganti baru', $lastLog->catatan);
    }

    public function test_selesai_unit_stays_in_servis_masuk_until_cetak_nota(): void
    {
        $this->actingAs($this->admin);

        $cust = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Rahmat', 'no_hp' => '0812334455']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00050',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $cust->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad X280',
            'keluhan' => 'Keyboard error',
            'status' => 'pengecekan',
            'is_cetak_nota' => false,
            'tanggal_masuk' => now(),
        ]);

        // 1. Selesaikan servis
        Livewire::test(ServisMasukIndex::class)
            ->call('openStatusModal', $servis->id)
            ->set('updateStatus', 'selesai')
            ->call('simpanUpdateStatus')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertEquals('selesai', $servis->status);
        $this->assertFalse($servis->is_cetak_nota);

        // 2. Unit tetap tampil di Servis Masuk karena belum dicetak nota
        Livewire::test(ServisMasukIndex::class)
            ->assertSee('Lenovo ThinkPad X280');

        // 3. Cetak Nota faktur servis
        Livewire::test(ServisMasukIndex::class)
            ->call('cetakNota', $servis->id)
            ->assertDispatched('open-print-invoice');

        $servis->refresh();
        $this->assertTrue($servis->is_cetak_nota);

        // 4. Setelah dicetak nota, unit tidak lagi muncul di Servis Masuk
        Livewire::test(ServisMasukIndex::class)
            ->assertDontSee('Lenovo ThinkPad X280');
    }

    public function test_can_render_tanda_terima_print_page(): void
    {
        $this->actingAs($this->admin);

        $cust = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Citra Kirana', 'no_hp' => '081999888777']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00099',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $cust->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Dell Inspiron 14',
            'keluhan' => 'Keyboard tidak berfungsi',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        $response = $this->get(route('servis.tanda-terima', $servis->id));
        $response->assertOk();
        $response->assertSee('TANDA TERIMA BARANG SERVIS');
        $response->assertSee('Dell Inspiron 14');
        $response->assertSee('Citra Kirana');
        $response->assertSee('www.soulmatekomputer.com');
    }

    public function test_can_add_jasa_and_sparepart_items_in_tindakan_modal(): void
    {
        $this->actingAs($this->admin);

        $cust = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Rian Hidayat', 'no_hp' => '081234567899']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00100',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $cust->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus ROG Strix',
            'keluhan' => 'Layar bergaris dan ganti SSD',
            'status' => 'perbaikan',
            'tanggal_masuk' => now(),
        ]);

        $jasa = \App\Models\MasterServis::create([
            'kode_item' => 'JS-001',
            'nama_item' => 'Jasa Ganti LCD & Repaste Thermal',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 0,
            'harga_jual' => 150000,
            'garansi_default_hari' => 30,
            'is_aktif' => true,
        ]);

        $kategori = \App\Models\KategoriProduk::create(['nama_kategori' => 'Storage']);
        $produk = \App\Models\Produk::create([
            'kategori_id' => $kategori->id,
            'nama_produk' => 'SSD NVMe 512GB Kingston',
            'harga_beli' => 450000,
            'harga_jual' => 600000,
            'barcode' => '89912345678',
        ]);
        \App\Models\Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 10,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        $stokCabang2 = \App\Models\Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang2->id,
            'jumlah' => 5,
            'min_stok' => 1,
            'is_aktif' => true,
        ]);

        // 1. Tambah Jasa Servis
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('setItemType', 'jasa')
            ->call('selectMasterItem', $jasa->id, $jasa->nama_item, 'jasa', $jasa->harga_jual, 30)
            ->call('tambahItemTindakan')
            ->assertHasNoErrors();

        // 2. Cek validasi jika qty melebihi stok fisik cabang
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('setItemType', 'sparepart')
            ->call('selectProdukItem', $produk->id, $produk->nama_produk, $produk->harga_jual, 10, 90)
            ->set('itemQty', 99)
            ->call('tambahItemTindakan')
            ->assertHasErrors(['itemQty']);

        // 3. Tambah Sparepart dari Master Produk (Qty = 2)
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('setItemType', 'sparepart')
            ->call('selectProdukItem', $produk->id, $produk->nama_produk, $produk->harga_jual, 10, 90)
            ->set('itemQty', 2)
            ->call('tambahItemTindakan')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertEquals(2, $servis->details()->count());
        $this->assertEquals(1350000, (float) $servis->total_biaya);

        // Assert stok cabang 1 berkurang dari 10 menjadi 8
        $stokCabang1 = \App\Models\Stok::where('produk_id', $produk->id)->where('cabang_id', $this->cabang1->id)->first();
        $this->assertEquals(8, $stokCabang1->jumlah);

        // Assert stok cabang 2 tetap 5 (terisolasi per cabang)
        $stokCabang2->refresh();
        $this->assertEquals(5, $stokCabang2->jumlah);

        // 4. Hapus item sparepart dari rincian -> Stok cabang 1 harus kembali jadi 10
        $sparepartDetail = $servis->details()->where('produk_id', $produk->id)->first();
        Livewire::test(ServisMasukIndex::class)
            ->call('openTindakanModal', $servis->id)
            ->call('hapusItemTindakan', $sparepartDetail->id);

        $stokCabang1->refresh();
        $this->assertEquals(10, $stokCabang1->jumlah);
    }
}
