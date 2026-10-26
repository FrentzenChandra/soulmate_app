<?php

namespace Tests\Feature;

use App\Livewire\KlaimGaransi\Index as KlaimGaransiIndex;
use App\Livewire\Master\Distributor\Index as DistributorIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KlaimGaransi;
use App\Models\MasterDistributor;
use App\Models\Pegawai;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KlaimGaransiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teknisi', 'guard_name' => 'web']);

        $this->cabang = Cabang::create([
            'nama_cabang' => 'Soulmate Pusat PKP',
            'kode_cabang' => 'CB-PKP',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'username' => 'owner',
            'email' => 'owner@soulmate.test',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->admin = User::create([
            'name' => 'Admin PKP',
            'username' => 'adminpkp',
            'email' => 'admin.pkp@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');

        foreach (['klaim_garansi.view', 'klaim_garansi.create', 'klaim_garansi.edit', 'klaim_garansi.delete', 'klaim_garansi.detail'] as $perm) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $this->admin->givePermissionTo(['klaim_garansi.view', 'klaim_garansi.create', 'klaim_garansi.edit', 'klaim_garansi.delete', 'klaim_garansi.detail']);
    }

    public function test_guests_cannot_access_klaim_garansi_or_distributor(): void
    {
        $this->get(route('klaim-garansi.index'))->assertRedirect(route('login'));
        $this->get(route('master.distributor'))->assertRedirect(route('login'));
    }

    public function test_authorized_owner_can_access_klaim_garansi_and_distributor(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('klaim-garansi.index'))->assertOk();
        $this->get(route('master.distributor'))->assertOk();
    }

    public function test_default_filter_is_bulanan_current_month(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(KlaimGaransiIndex::class)
            ->assertSet('periodeMode', 'bulanan')
            ->assertSet('filterBulan', Carbon::now()->format('m'))
            ->assertSet('filterTahun', (string) Carbon::now()->year);
    }

    public function test_zero_status_architecture_verification(): void
    {
        // Pastikan tabel klaim_garansis sama sekali TIDAK MEMILIKI kolom 'status'
        $this->assertFalse(
            Schema::hasColumn('klaim_garansis', 'status'),
            'Tabel klaim_garansis TIDAK BOLEH memiliki kolom status sesuai Zero Status Architecture'
        );

        $this->actingAs($this->owner);

        // Halaman Klaim Garansi tidak boleh menampilkan badge status atau select filter status
        $response = $this->get(route('klaim-garansi.index'));
        $response->assertOk();
        $response->assertDontSee('Semua Status');
        $response->assertDontSee('Belum Lunas');
    }

    public function test_can_create_klaim_garansi_with_snapshot_and_no_stock_mutation(): void
    {
        $this->actingAs($this->owner);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Pelanggan Setia Antigravity',
            'no_hp' => '081299998888',
        ]);

        $distributor = MasterDistributor::create([
            'nama' => 'ASUS Indonesia Service Center',
            'is_aktif' => true,
        ]);

        $pegawai = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Teknisi Handal Frentzen',
            'role' => 'Teknisi Laptop',
            'is_aktif' => true,
        ]);

        $pengirim = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Kurir Cepat Budi',
            'role' => 'Operasional',
            'is_aktif' => true,
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Motherboard ROG Strix B550',
            'barcode' => 'ROG-B550-TEST',
            'harga_beli' => 2000000,
            'harga_jual' => 2500000,
        ]);

        $stok = Stok::create([
            'cabang_id' => $this->cabang->id,
            'produk_id' => $produk->id,
            'jumlah' => 10,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        $stokAwal = $stok->jumlah;

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openCreateModal')
            ->set('tanggalMasuk', '2026-10-21')
            ->set('customerType', 'langganan')
            ->call('selectCustomer', $customer->id, $customer->nama, $customer->no_hp)
            ->call('selectProduct', $produk->nama_produk)
            ->set('formKeluhan', 'Port PCIe pertama tidak mendeteksi GPU secara intermiten')
            ->set('formMasterDistributorId', $distributor->id)
            ->set('formTanggalKirim', '2026-10-22')
            ->set('formTindakan', 'Kirim unit ke distributor pusat Jakarta')
            ->set('formTanggalSelesaiKlaim', '2026-10-25')
            ->set('formTanggalBarangKembali', '2026-10-26')
            ->call('save')
            ->assertHasNoErrors();

        // 1. Verifikasi data tersimpan di DB beserta snapshot historis
        $this->assertDatabaseHas('klaim_garansis', [
            'customer_nama' => 'Pelanggan Setia Antigravity',
            'customer_kontak' => '081299998888',
            'barang' => 'Motherboard ROG Strix B550',
            'distributor_nama' => 'ASUS Indonesia Service Center',
            'pengirim_nama' => 'Owner Utama',
            'penerima_nama' => null,
        ]);

        $klaim = KlaimGaransi::where('customer_nama', 'Pelanggan Setia Antigravity')->first();
        $this->assertNotNull($klaim);
        $this->assertEquals('2026-10-21', $klaim->tanggal_masuk->format('Y-m-d'));
        $this->assertEquals('2026-10-25', $klaim->tanggal_selesai_klaim->format('Y-m-d'));

        // 2. Verifikasi TIDAK ADA mutasi stok barang (Zero stock mutation)
        $this->assertEquals($stokAwal, $stok->fresh()->jumlah, 'Stok produk tidak boleh berkurang sama sekali saat klaim garansi dicatat');
    }

    public function test_can_create_klaim_garansi_with_new_customer_registration(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openCreateModal')
            ->set('tanggalMasuk', '2026-10-21')
            ->set('customerType', 'baru')
            ->set('newCustomerNama', 'Customer Walkin Baru')
            ->set('newCustomerKontak', '081377776666')
            ->set('formBarang', 'Keyboard Logitech Mechanical G512')
            ->set('formKeluhan', 'Tombol W dan D double-click')
            ->call('save')
            ->assertHasNoErrors();

        // Verifikasi customer baru otomatis tercipta di database
        $this->assertDatabaseHas('customers', [
            'nama' => 'Customer Walkin Baru',
            'no_hp' => '081377776666',
        ]);

        $this->assertDatabaseHas('klaim_garansis', [
            'customer_nama' => 'Customer Walkin Baru',
            'barang' => 'Keyboard Logitech Mechanical G512',
        ]);
    }

    public function test_can_create_klaim_garansi_defaults_tanggal_masuk_to_today(): void
    {
        $this->actingAs($this->owner);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Budi Santoso',
            'no_hp' => '081234567890',
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openCreateModal')
            ->call('selectCustomer', $customer->id, $customer->nama, $customer->no_hp)
            ->set('formBarang', 'Laptop ASUS TUF Gaming')
            ->set('formKeluhan', 'Mati total tidak bisa dihidupkan')
            ->call('save')
            ->assertHasNoErrors();

        $klaim = KlaimGaransi::where('customer_nama', 'Budi Santoso')->first();
        $this->assertNotNull($klaim);
        $this->assertEquals(Carbon::today()->toDateString(), $klaim->tanggal_masuk->toDateString());
        $this->assertEquals($this->owner->name, $klaim->pengirim_nama);
        $this->assertNull($klaim->penerima_nama);
    }

    public function test_can_edit_klaim_garansi(): void
    {
        $this->actingAs($this->owner);

        $klaim = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => KlaimGaransi::generateNoKlaim($this->cabang->id),
            'tanggal_masuk' => '2026-10-10',
            'customer_nama' => 'Andi Wijaya',
            'barang' => 'Monitor LG 24 Inch',
            'keluhan' => 'Layar bergaris vertikal',
            'pengirim_nama' => 'Pengirim Awal',
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->assertViewHas('tableData')
            ->call('openEditModal', $klaim->id)
            ->assertSet('formBarang', 'Monitor LG 24 Inch')
            ->call('resetSelectedProduct')
            ->assertSet('formBarang', '')
            ->assertSet('barangSearch', '')
            ->call('selectProduct', 'Monitor LG 27 Inch Upgrade')
            ->assertSet('formBarang', 'Monitor LG 27 Inch Upgrade')
            ->set('formTindakan', 'Panel LCD diganti baru oleh SC')
            ->set('formTanggalSelesaiKlaim', '2026-10-21')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('klaim_garansis', [
            'id' => $klaim->id,
            'barang' => 'Monitor LG 27 Inch Upgrade',
            'tindakan' => 'Panel LCD diganti baru oleh SC',
            'pengirim_nama' => 'Pengirim Awal',
        ]);

        $this->assertEquals('2026-10-21', $klaim->fresh()->tanggal_selesai_klaim->format('Y-m-d'));
    }

    public function test_can_tandai_selesai_klaim_with_today_date_and_toggle_back(): void
    {
        $this->actingAs($this->owner);

        $klaim = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => KlaimGaransi::generateNoKlaim($this->cabang->id),
            'tanggal_masuk' => '2026-10-10',
            'customer_nama' => 'Raffi Ahmad',
            'barang' => 'VGA RTX 4090',
            'keluhan' => 'Kipas tidak berputar',
            'tanggal_selesai_klaim' => null,
        ]);

        $this->assertNull($klaim->tanggal_selesai_klaim);

        // 1. Tandai selesai -> hari ini & penerima = user login
        Livewire::test(KlaimGaransiIndex::class)
            ->call('tandaiSelesaiKlaim', $klaim->id)
            ->assertDispatched('swal:toast')
            ->assertDispatched('klaim-garansi-table-updated');

        $this->assertEquals(
            Carbon::today()->toDateString(),
            $klaim->fresh()->tanggal_selesai_klaim->toDateString()
        );
        $this->assertEquals($this->owner->name, $klaim->fresh()->penerima_nama);
        $this->assertEquals(
            Carbon::today()->toDateString(),
            $klaim->fresh()->tanggal_barang_kembali->toDateString()
        );

        // 2. Toggle kembali -> null (Belum selesai & penerima null)
        Livewire::test(KlaimGaransiIndex::class)
            ->call('tandaiSelesaiKlaim', $klaim->id)
            ->assertDispatched('swal:toast')
            ->assertDispatched('klaim-garansi-table-updated');

        $this->assertNull($klaim->fresh()->tanggal_selesai_klaim);
        $this->assertNull($klaim->fresh()->penerima_nama);
        $this->assertNull($klaim->fresh()->penerima_pegawai_id);
    }

    public function test_auto_assigns_pegawai_id_when_matching_pegawai_record_exists(): void
    {
        $pegawaiOwner = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => $this->owner->name,
            'role' => 'Superadmin',
            'is_aktif' => true,
        ]);

        $pegawaiAdmin = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => $this->admin->name,
            'role' => 'Admin',
            'is_aktif' => true,
        ]);

        // 1. Admin login dan buat klaim baru
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Siti Badriah',
            'no_hp' => '081299998888',
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openCreateModal')
            ->call('selectCustomer', $customer->id, $customer->nama, $customer->no_hp)
            ->set('formBarang', 'Printer Canon G2010')
            ->set('formKeluhan', 'Head macet')
            ->call('save')
            ->assertHasNoErrors();

        $klaim = KlaimGaransi::where('customer_nama', 'Siti Badriah')->first();
        $this->assertNotNull($klaim);
        $this->assertEquals($this->admin->name, $klaim->pengirim_nama);
        $this->assertEquals($pegawaiAdmin->id, $klaim->pengirim_pegawai_id);
        $this->assertNull($klaim->penerima_nama);
        $this->assertNull($klaim->penerima_pegawai_id);

        // 2. Owner login dan tandai selesai via tombol centang
        $this->actingAs($this->owner);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('tandaiSelesaiKlaim', $klaim->id)
            ->assertDispatched('swal:toast');

        $fresh = $klaim->fresh();
        $this->assertEquals($this->owner->name, $fresh->penerima_nama);
        $this->assertEquals($pegawaiOwner->id, $fresh->penerima_pegawai_id);
        $this->assertEquals(Carbon::today()->toDateString(), $fresh->tanggal_selesai_klaim->toDateString());
    }

    public function test_table_data_sorts_by_latest_tanggal_masuk_and_latest_id(): void
    {
        $this->actingAs($this->owner);

        // Klaim 1: tanggal lama
        $klaimLama = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => 'KG-OLD-001',
            'tanggal_masuk' => '2026-10-01',
            'customer_nama' => 'Pelanggan Lama',
            'barang' => 'RAM DDR4 8GB',
            'keluhan' => 'Bluescreen',
        ]);

        // Klaim 2: tanggal baru
        $klaimBaru = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => 'KG-NEW-002',
            'tanggal_masuk' => '2026-10-25',
            'customer_nama' => 'Pelanggan Baru',
            'barang' => 'SSD NVMe 1TB',
            'keluhan' => 'Not detected',
        ]);

        $test = Livewire::test(KlaimGaransiIndex::class)
            ->set('periodeMode', 'semua');

        $data = $test->instance()->getTableData();
        $this->assertNotEmpty($data);
        $this->assertEquals($klaimBaru->id, $data[0]['id'], 'Klaim dengan tanggal masuk terbaru harus berada di baris pertama');
    }

    public function test_can_soft_delete_klaim_garansi(): void
    {
        $this->actingAs($this->owner);

        $klaim = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => KlaimGaransi::generateNoKlaim($this->cabang->id),
            'tanggal_masuk' => '2026-10-10',
            'customer_nama' => 'Dedi Corbuzier',
            'barang' => 'Microphone Shure MV7',
            'keluhan' => 'Suara kemresek',
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('confirmDelete', $klaim->id, $klaim->no_klaim)
            ->call('deleteKlaim')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('klaim_garansis', [
            'id' => $klaim->id,
        ]);
    }

    public function test_master_distributor_crud(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Distributor
        Livewire::test(DistributorIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'PT ECS Indo Jaya')
            ->set('is_aktif', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('master_distributors', [
            'nama' => 'PT ECS Indo Jaya',
            'is_aktif' => true,
        ]);

        $dist = MasterDistributor::where('nama', 'PT ECS Indo Jaya')->first();

        // 2. Edit Distributor
        Livewire::test(DistributorIndex::class)
            ->call('edit', $dist->id)
            ->set('nama', 'PT ECS Indo Jaya Authorized')
            ->set('is_aktif', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('master_distributors', [
            'id' => $dist->id,
            'nama' => 'PT ECS Indo Jaya Authorized',
            'is_aktif' => false,
        ]);

        // 3. Soft Delete Distributor
        Livewire::test(DistributorIndex::class)
            ->call('delete', $dist->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('master_distributors', [
            'id' => $dist->id,
        ]);
    }
}
