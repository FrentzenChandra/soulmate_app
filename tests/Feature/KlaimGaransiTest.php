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

    public function test_table_data_sorts_by_priority_tanggal_selesai_desc_then_tanggal_masuk_desc(): void
    {
        $this->actingAs($this->owner);

        // Klaim 1: belum selesai, tanggal masuk 2026-10-01
        $klaim1 = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => 'KG-OLD-001',
            'tanggal_masuk' => '2026-10-01',
            'customer_nama' => 'Pelanggan 1',
            'barang' => 'RAM DDR4 8GB',
            'keluhan' => 'Bluescreen',
        ]);

        // Klaim 2: belum selesai, tanggal masuk 2026-10-25
        $klaim2 = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => 'KG-NEW-002',
            'tanggal_masuk' => '2026-10-25',
            'customer_nama' => 'Pelanggan 2',
            'barang' => 'SSD NVMe 1TB',
            'keluhan' => 'Not detected',
        ]);

        // Klaim 3: selesai tanggal 2026-10-26, tanggal masuk 2026-10-10
        $klaim3 = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_klaim' => 'KG-DONE-003',
            'tanggal_masuk' => '2026-10-10',
            'customer_nama' => 'Pelanggan 3',
            'barang' => 'VGA RTX 3060',
            'keluhan' => 'Artefak',
            'tanggal_selesai_klaim' => '2026-10-26',
        ]);

        $test = Livewire::test(KlaimGaransiIndex::class)
            ->set('periodeMode', 'semua');

        $data = $test->instance()->getTableData();
        $this->assertNotEmpty($data);

        // Prioritas 1: tanggal_selesai_klaim DESC -> $klaim3 harus paling atas
        $this->assertEquals($klaim3->id, $data[0]['id'], 'Klaim dengan tanggal selesai klaim harus berada di baris pertama');

        // Prioritas 2: untuk yang belum selesai, tanggal_masuk DESC -> $klaim2 lebih baru dari $klaim1
        $this->assertEquals($klaim2->id, $data[1]['id'], 'Klaim dengan tanggal masuk terbaru berada di baris berikutnya');
        $this->assertEquals($klaim1->id, $data[2]['id'], 'Klaim dengan tanggal masuk lama berada di baris terakhir');
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

    // =========================================================================
    // TEST KARTU RINGKASAN METRIK (TERKUNCI DI BULAN BERJALAN)
    // =========================================================================

    public function test_summary_cards_display_current_month_claims_and_math(): void
    {
        $this->actingAs($this->owner);
        $now = Carbon::now('Asia/Jakarta');
        $currentMonthName = $now->locale('id')->translatedFormat('F Y');

        $distributor = MasterDistributor::create(['nama' => 'Distributor A', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Budi Santoso', 'no_hp' => '08123456789', 'cabang_id' => $this->cabang->id]);

        // Klaim 1: Belum selesai di bulan ini
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-CURR-01',
            'tanggal_masuk' => $now->copy()->startOfMonth()->addDays(2)->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'RAM DDR4 8GB',
            'keluhan' => 'Bluescreen',
            'tanggal_selesai_klaim' => null,
        ]);

        // Klaim 2: Selesai di bulan ini
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-CURR-02',
            'tanggal_masuk' => $now->copy()->startOfMonth()->addDays(5)->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'SSD 512GB NVMe',
            'keluhan' => 'Tidak terbaca',
            'tanggal_selesai_klaim' => $now->toDateString(),
        ]);

        // Klaim 3: Selesai di bulan ini
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-CURR-03',
            'tanggal_masuk' => $now->copy()->startOfMonth()->addDays(8)->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'Motherboard H610',
            'keluhan' => 'Mati total',
            'tanggal_selesai_klaim' => $now->toDateString(),
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->assertSeeHtml('Total Klaim')
            ->assertSeeHtml('Selesai Klaim')
            ->assertSeeHtml('Belum Selesai Klaim')
            ->assertSeeHtml('klaim')
            ->assertDontSee('Bulan ' . $currentMonthName)
            ->assertSeeHtml('3') // Total Klaim
            ->assertSeeHtml('2') // Selesai Klaim
            ->assertSeeHtml('1'); // Belum Selesai Klaim
    }

    public function test_summary_cards_exclude_previous_month_end_and_next_month_start(): void
    {
        $this->actingAs($this->owner);
        $now = Carbon::now('Asia/Jakarta');
        $distributor = MasterDistributor::create(['nama' => 'Distributor B', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Siti', 'no_hp' => '08129876543', 'cabang_id' => $this->cabang->id]);

        $lastMonthEnd = $now->copy()->startOfMonth()->subDay()->toDateString();
        $nextMonthStart = $now->copy()->endOfMonth()->addDay()->toDateString();

        // Klaim bulan lalu (tidak boleh dihitung)
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-PREV-MONTH',
            'tanggal_masuk' => $lastMonthEnd,
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'VGA RTX 3060',
            'keluhan' => 'No display',
            'tanggal_selesai_klaim' => null,
        ]);

        // Klaim bulan depan (tidak boleh dihitung)
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-NEXT-MONTH',
            'tanggal_masuk' => $nextMonthStart,
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'Monitor 24 Inch',
            'keluhan' => 'Garis horizontal',
            'tanggal_selesai_klaim' => null,
        ]);

        // Klaim sah bulan ini (1 klaim belum selesai)
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-THIS-MONTH',
            'tanggal_masuk' => $now->copy()->startOfMonth()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'PSU 650W',
            'keluhan' => 'Drop voltase',
            'tanggal_selesai_klaim' => null,
        ]);

        $component = Livewire::test(KlaimGaransiIndex::class);
        $this->assertEquals(1, $component->viewData('totalKlaim'));
        $this->assertEquals(0, $component->viewData('selesaiKlaim'));
        $this->assertEquals(1, $component->viewData('belumSelesaiKlaim'));
    }

    public function test_summary_cards_exclude_soft_deleted_claims(): void
    {
        $this->actingAs($this->owner);
        $now = Carbon::now('Asia/Jakarta');
        $distributor = MasterDistributor::create(['nama' => 'Distributor C', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Agus', 'no_hp' => '08121112223', 'cabang_id' => $this->cabang->id]);

        $klaim = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-DELETED',
            'tanggal_masuk' => $now->copy()->startOfMonth()->addDay()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'Laptop Ideapad',
            'keluhan' => 'Keyboard rusak',
            'tanggal_selesai_klaim' => null,
        ]);

        $klaim->delete(); // Soft delete

        $component = Livewire::test(KlaimGaransiIndex::class);
        $this->assertEquals(0, $component->viewData('totalKlaim'));
        $this->assertEquals(0, $component->viewData('selesaiKlaim'));
        $this->assertEquals(0, $component->viewData('belumSelesaiKlaim'));
    }

    public function test_table_period_and_search_filters_do_not_affect_summary_cards(): void
    {
        $this->actingAs($this->owner);
        $now = Carbon::now('Asia/Jakarta');
        $distributor = MasterDistributor::create(['nama' => 'Distributor D', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Joko', 'no_hp' => '08133344455', 'cabang_id' => $this->cabang->id]);

        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-LOCK-1',
            'tanggal_masuk' => $now->copy()->startOfMonth()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'Printer L3210',
            'keluhan' => 'Blinking',
            'tanggal_selesai_klaim' => null,
        ]);

        // Ubah mode periode ke hari ini dan ubah filter pencarian tabel ke kata kunci yang tidak ada
        $component = Livewire::test(KlaimGaransiIndex::class)
            ->call('setPeriodeMode', 'hari_ini')
            ->set('search', 'NONEXISTENT_KEYWORD_XYZ');

        $this->assertEquals(1, $component->viewData('totalKlaim'));
        $this->assertEquals(0, $component->viewData('selesaiKlaim'));
        $this->assertEquals(1, $component->viewData('belumSelesaiKlaim'));
    }

    public function test_summary_cards_branch_isolation(): void
    {
        $cabang2 = Cabang::create([
            'nama_cabang' => 'Soulmate Sungailiat',
            'kode_cabang' => 'CB-SGL',
            'status' => 'aktif',
        ]);

        $stafCabang1 = User::create([
            'name' => 'Staf Cabang 1',
            'username' => 'staf1',
            'email' => 'staf1@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $stafCabang1->assignRole('Admin');
        $stafCabang1->givePermissionTo('klaim_garansi.view');

        $now = Carbon::now('Asia/Jakarta');
        $distributor = MasterDistributor::create(['nama' => 'Distributor E', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Doni', 'no_hp' => '08177788899', 'cabang_id' => $this->cabang->id]);

        // Klaim di Cabang 1
        KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-CAB1',
            'tanggal_masuk' => $now->copy()->startOfMonth()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'RAM DDR5 16GB',
            'keluhan' => 'Error memtest',
            'tanggal_selesai_klaim' => null,
        ]);

        // Klaim di Cabang 2
        KlaimGaransi::create([
            'cabang_id' => $cabang2->id,
            'no_klaim' => 'KG-CAB2',
            'tanggal_masuk' => $now->copy()->startOfMonth()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'GPU RX 6600',
            'keluhan' => 'Artifak',
            'tanggal_selesai_klaim' => $now->toDateString(),
        ]);

        // Staf Cabang 1 hanya melihat 1 klaim (milik cabang 1)
        $this->actingAs($stafCabang1);
        $componentStaf = Livewire::test(KlaimGaransiIndex::class);
        $this->assertEquals(1, $componentStaf->viewData('totalKlaim'));
        $this->assertEquals(0, $componentStaf->viewData('selesaiKlaim'));
        $this->assertEquals(1, $componentStaf->viewData('belumSelesaiKlaim'));

        // Owner di HQ dengan cabangFilter 'semua' melihat 2 klaim (total cabang 1 + cabang 2)
        $this->actingAs($this->owner);
        $componentOwnerAll = Livewire::test(KlaimGaransiIndex::class)
            ->set('cabangFilter', 'semua');
        $this->assertEquals(2, $componentOwnerAll->viewData('totalKlaim'));
        $this->assertEquals(1, $componentOwnerAll->viewData('selesaiKlaim'));
        $this->assertEquals(1, $componentOwnerAll->viewData('belumSelesaiKlaim'));

        // Owner memilih cabang 2 melihat 1 klaim (milik cabang 2)
        $componentOwnerCabang2 = Livewire::test(KlaimGaransiIndex::class)
            ->set('cabangFilter', (string) $cabang2->id);
        $this->assertEquals(1, $componentOwnerCabang2->viewData('totalKlaim'));
        $this->assertEquals(1, $componentOwnerCabang2->viewData('selesaiKlaim'));
        $this->assertEquals(0, $componentOwnerCabang2->viewData('belumSelesaiKlaim'));
    }

    public function test_summary_cards_live_update_when_claim_is_marked_completed(): void
    {
        $this->actingAs($this->owner);
        $now = Carbon::now('Asia/Jakarta');
        $distributor = MasterDistributor::create(['nama' => 'Distributor F', 'is_aktif' => true]);
        $customer = Customer::create(['nama' => 'Rina', 'no_hp' => '08155566677', 'cabang_id' => $this->cabang->id]);

        $klaim = KlaimGaransi::create([
            'cabang_id' => $this->cabang->id,
            'no_klaim' => 'KG-TOGGLE-TEST',
            'tanggal_masuk' => $now->copy()->startOfMonth()->toDateString(),
            'customer_id' => $customer->id,
            'customer_nama' => $customer->nama,
            'master_distributor_id' => $distributor->id,
            'barang' => 'SSD 1TB M.2',
            'keluhan' => 'Read only',
            'tanggal_selesai_klaim' => null,
        ]);

        $component = Livewire::test(KlaimGaransiIndex::class);
        $this->assertEquals(1, $component->viewData('totalKlaim'));
        $this->assertEquals(0, $component->viewData('selesaiKlaim'));
        $this->assertEquals(1, $component->viewData('belumSelesaiKlaim'));

        // Panggil tandaiSelesaiKlaim
        $component->call('tandaiSelesaiKlaim', $klaim->id);

        $this->assertEquals(1, $component->viewData('totalKlaim'));
        $this->assertEquals(1, $component->viewData('selesaiKlaim'));
        $this->assertEquals(0, $component->viewData('belumSelesaiKlaim'));
    }
}
