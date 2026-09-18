<?php

namespace Tests\Feature;

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

class ServisPengerjaanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $teknisi;
    protected Cabang $cabang1;
    protected Cabang $cabang2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner']);
        Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Teknisi']);
        Role::firstOrCreate(['name' => 'Kasir']);

        $this->cabang1 = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'alamat' => 'Jl. Sudirman No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->cabang2 = Cabang::create([
            'kode_cabang' => 'CB-SBL',
            'nama_cabang' => 'Cabang Sungailiat',
            'alamat' => 'Jl. Raya Sungailiat',
            'no_wa' => '081298765432',
            'status' => 'aktif',
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Test',
            'email' => 'owner@example.com',
            'username' => 'owner_test',
            'cabang_id' => null,
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'username' => 'admin_test',
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');

        $this->teknisi = User::factory()->create([
            'name' => 'Teknisi Budi',
            'email' => 'teknisi@example.com',
            'username' => 'teknisi_budi',
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
        ]);
        $this->teknisi->assignRole('Teknisi');
    }

    public function test_guests_cannot_access_servis_pengerjaan(): void
    {
        $response = $this->get(route('servis.pengerjaan'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_access_servis_pengerjaan(): void
    {
        $this->actingAs($this->teknisi);
        $response = $this->get(route('servis.pengerjaan'));
        $response->assertOk();
        $response->assertSee('Servis Keluar');
    }

    public function test_can_add_jasa_and_sparepart_with_billing_calculation(): void
    {
        $this->actingAs($this->teknisi);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Budi Customer', 'no_hp' => '081233445566']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00001',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad X270',
            'keluhan' => 'Keyboard rusak & lambat',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
            'estimasi_biaya' => 0,
            'total_biaya' => 0,
        ]);

        $itemJasa = \App\Models\MasterServis::create([
            'kode_item' => 'JS-001',
            'nama_item' => 'Jasa Ganti Keyboard & Cleaning',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 0,
            'harga_jual' => 75000,
            'garansi_default_hari' => 30,
            'is_aktif' => true,
        ]);

        $itemSparepart = \App\Models\MasterServis::create([
            'kode_item' => 'SP-001',
            'nama_item' => 'SSD M.2 NVMe 512GB',
            'jenis' => 'sparepart',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 300000,
            'harga_jual' => 450000,
            'garansi_default_hari' => 365,
            'is_aktif' => true,
        ]);

        // Livewire interaction: Buka workspace, tambah jasa & tambah sparepart via Master Servis
        Livewire::test(\App\Livewire\Servis\Pengerjaan\Index::class)
            ->call('openPengerjaanModal', $servis->id)
            ->assertSet('modalPengerjaanOpen', true)
            // Tambah Jasa (dari Master Servis)
            ->call('selectMasterServis', $itemJasa->id)
            ->set('sparepartQty', 1)
            ->call('tambahItemBilling')
            ->assertHasNoErrors()
            // Tambah Sparepart
            ->call('selectMasterServis', $itemSparepart->id)
            ->set('sparepartQty', 1)
            ->call('tambahItemBilling')
            ->assertHasNoErrors()
            // Update status ke Selesai
            ->set('pengerjaanStatus', 'selesai')
            ->call('simpanPengerjaan')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertEquals(525000, $servis->total_biaya);
        $this->assertEquals(75000, $servis->total_jasa);
        $this->assertEquals(450000, $servis->total_sparepart);
        $this->assertEquals('selesai', $servis->status);
        $this->assertNotNull($servis->tanggal_selesai);
    }

    public function test_can_process_cashier_payment_with_warranty(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Dewi Lestari', 'no_hp' => '0812999888']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00002',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L3110',
            'keluhan' => 'Hasil cetak garis',
            'status' => 'selesai',
            'tanggal_masuk' => now()->subDay(),
            'tanggal_selesai' => now(),
            'total_biaya' => 150000,
        ]);

        ServisDetail::create([
            'servis_id' => $servis->id,
            'produk_id' => null,
            'nama_item' => 'Jasa Cleaning Head & Reset Waste Pad',
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
        ]);

        Livewire::test(\App\Livewire\Servis\Pengerjaan\Index::class)
            ->call('openCheckoutModal', $servis->id)
            ->assertSet('modalCheckoutOpen', true)
            ->call('setGaransi', 30)
            ->call('simpanPelunasan')
            ->assertHasNoErrors()
            ->assertDispatched('open-print-invoice');

        $servis->refresh();
        $this->assertEquals('sudah_diambil', $servis->status);
        $this->assertEquals(150000, $servis->bayar);
        $this->assertEquals(0, $servis->kembalian);
        $this->assertEquals(30, $servis->garansi_hari);
        $this->assertNotNull($servis->garansi_sampai);
        $this->assertEquals($this->admin->id, $servis->kasir_id);
        $this->assertNotNull($servis->tanggal_ambil);
    }

    public function test_can_render_servis_invoice_page(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Rian Hidayat', 'no_hp' => '0812777888']);
        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00003',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'teknisi_id' => $this->teknisi->id,
            'kasir_id' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus Vivobook 14',
            'keluhan' => 'Engsel patah',
            'status' => 'sudah_dibayar',
            'tanggal_masuk' => now()->subDays(2),
            'tanggal_selesai' => now()->subDay(),
            'tanggal_ambil' => now(),
            'total_biaya' => 250000,
            'metode_pembayaran' => 'transfer',
            'bayar' => 250000,
            'kembalian' => 0,
            'garansi_hari' => 14,
            'garansi_sampai' => now()->addDays(14)->toDateString(),
        ]);

        $response = $this->get(route('servis.invoice', $servis->id));
        $response->assertOk();
        $response->assertSee('FAKTUR PELUNASAN SERVIS');
        $response->assertSee('Asus Vivobook 14');
        $response->assertSee('Rian Hidayat');
        $response->assertSee('GARANSI TOKO RESMI (14 HARI)');
    }

    public function test_displays_tanggal_ambil_and_filters_correctly(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'User Ambil Test', 'no_hp' => '0812332211']);

        // Servis 1: Belum diambil
        $servisBelum = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00004',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Ideapad Slim',
            'keluhan' => 'Keyboard error',
            'status' => 'selesai',
            'tanggal_masuk' => now()->subDay(),
            'tanggal_selesai' => now(),
            'tanggal_ambil' => null,
            'total_biaya' => 100000,
        ]);

        // Servis 2: Sudah diambil
        $servisSudah = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00005',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Gaming Custom',
            'keluhan' => 'Thermal paste renewal',
            'status' => 'sudah_dibayar',
            'tanggal_masuk' => now()->subDays(3),
            'tanggal_selesai' => now()->subDays(2),
            'tanggal_ambil' => now()->subDay(),
            'total_biaya' => 200000,
        ]);

        // Default: see both and see "Belum Diambil"
        Livewire::test(\App\Livewire\Servis\Pengerjaan\Index::class)
            ->assertSee('Lenovo Ideapad Slim')
            ->assertSee('PC Gaming Custom')
            ->assertSee('Belum Diambil')
            ->assertSee($servisSudah->tanggal_ambil->format('d/m/Y'))
            // Filter: belum_diambil
            ->set('ambilFilter', 'belum_diambil')
            ->assertSee('Lenovo Ideapad Slim')
            ->assertDontSee('PC Gaming Custom')
            // Filter: sudah_diambil
            ->set('ambilFilter', 'sudah_diambil')
            ->assertSee('PC Gaming Custom')
            ->assertDontSee('Lenovo Ideapad Slim');
    }
}
