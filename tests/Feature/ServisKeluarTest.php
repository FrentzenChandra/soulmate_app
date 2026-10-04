<?php

namespace Tests\Feature;

use App\Livewire\Servis\Keluar\Index as ServisKeluarIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterServis;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServisKeluarTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $teknisi;
    protected Cabang $cabang1;

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

    public function test_guests_cannot_access_servis_keluar(): void
    {
        $response = $this->get(route('servis.keluar'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_access_servis_keluar(): void
    {
        $this->actingAs($this->teknisi);
        $response = $this->get(route('servis.keluar'));
        $response->assertOk();
        $response->assertSee('Servis Keluar');
    }

    public function test_displays_selesai_and_sudah_diambil_with_sorting(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Budi', 'no_hp' => '081234']);

        $servisSelesai = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00001',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus ROG Strix',
            'keluhan' => 'Layar bergaris',
            'status' => 'selesai',
            'is_cetak_nota' => true,
            'tanggal_masuk' => now()->subDays(2),
            'tanggal_selesai' => now(),
            'total_biaya' => 250000,
        ]);

        $servisSudahDiambil = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00002',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L3110',
            'keluhan' => 'Head buntu',
            'status' => 'sudah_diambil',
            'is_cetak_nota' => true,
            'tanggal_masuk' => now()->subDays(5),
            'tanggal_selesai' => now()->subDays(3),
            'tanggal_ambil' => now()->subDays(1),
            'total_biaya' => 150000,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->assertSee('Asus ROG Strix')
            ->assertSee('Epson L3110')
            ->assertSee('Belum Diambil')
            ->assertSee('Sudah Diambil')
            ->assertSeeInOrder(['Asus ROG Strix', 'Epson L3110']);
    }

    public function test_selesai_without_cetak_nota_is_not_displayed_in_servis_keluar(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Budi', 'no_hp' => '081234']);

        $servisBelumCetak = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00099',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'HP Omen Gaming',
            'keluhan' => 'Ganti thermal paste',
            'status' => 'selesai',
            'is_cetak_nota' => false,
            'tanggal_masuk' => now()->subDays(1),
            'tanggal_selesai' => now(),
            'total_biaya' => 100000,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->assertDontSee('HP Omen Gaming');
    }

    public function test_can_process_penyerahan_and_payment(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Rian', 'no_hp' => '081999888777']);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00003',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Acer Swift 3',
            'keluhan' => 'Baterai drop',
            'status' => 'selesai',
            'tanggal_masuk' => now(),
            'tanggal_selesai' => now(),
            'total_biaya' => 200000,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openAmbilModal', $servis->id)
            ->assertSet('modalAmbilOpen', true)
            ->set('metodePembayaran', 'cash')
            ->set('nominalBayar', 250000)
            ->call('prosesPenyerahanUnit')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertEquals('sudah_diambil', $servis->status);
        $this->assertNotNull($servis->tanggal_ambil);
        $this->assertEquals(50000, (float) $servis->kembalian);
    }

    public function test_can_add_item_with_warranty_in_edit_nota(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Rian', 'no_hp' => '081999888777']);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00004',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Ideapad',
            'keluhan' => 'Ganti LCD',
            'status' => 'selesai',
            'tanggal_masuk' => now(),
            'tanggal_selesai' => now(),
            'total_biaya' => 0,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openEditNotaModal', $servis->id)
            ->assertSet('modalEditNotaOpen', true)
            ->set('selectedMasterServisNama', 'LCD IPS 14 Inch')
            ->set('selectedMasterServisJenis', 'sparepart')
            ->set('selectedMasterServisHarga', 850000)
            ->set('itemQty', 1)
            ->set('itemGaransiHari', 30)
            ->call('tambahItemNota')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertEquals(850000, (float) $servis->total_biaya);
        $this->assertCount(1, $servis->details);
        $this->assertEquals(30, $servis->details->first()->garansi_hari);
        $this->assertEquals('30 Hari', $servis->details->first()->garansi_label);
    }

    public function test_cannot_edit_nota_when_status_is_sudah_diambil(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Doni', 'no_hp' => '081223344']);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00005',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'HP Pavilion',
            'keluhan' => 'Ganti Pasta',
            'status' => 'sudah_diambil',
            'tanggal_masuk' => now()->subDays(2),
            'tanggal_selesai' => now()->subDays(1),
            'tanggal_ambil' => now(),
            'total_biaya' => 100000,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openEditNotaModal', $servis->id)
            ->assertSet('modalEditNotaOpen', false)
            ->assertDispatched('swal:modal');
    }
}
