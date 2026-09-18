<?php

namespace Tests\Feature;

use App\Livewire\Servis\Batal\Index as ServisBatalIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServisBatalTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
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

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'username' => 'admin_test',
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_guests_cannot_access_servis_batal(): void
    {
        $response = $this->get(route('servis.batal'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_access_servis_batal(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('servis.batal'));
        $response->assertOk();
        $response->assertSee('Servis Batal');
    }

    public function test_displays_batal_with_sorting_belum_diambil_first(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Hendra', 'no_hp' => '08111222333']);

        $servisBelumAmbil = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00004',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Legion 5',
            'keluhan' => 'Motherboard terbakar',
            'status' => 'batal',
            'tanggal_masuk' => now(),
            'tanggal_ambil' => null,
        ]);

        $servisSudahAmbil = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00005',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Canon IP2770',
            'keluhan' => 'Sparepart discontinue',
            'status' => 'batal',
            'tanggal_masuk' => now()->subDays(3),
            'tanggal_ambil' => now()->subDays(1),
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->assertSee('Lenovo Legion 5')
            ->assertSee('Canon IP2770')
            ->assertSee('Fisik Belum Diambil')
            ->assertSee('Fisik Sudah Diambil');
    }

    public function test_can_process_penyerahan_unit_batal_without_fee(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create(['cabang_id' => $this->cabang1->id, 'nama' => 'Joko', 'no_hp' => '081555666777']);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-260915-00006',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Rakitan Core i3',
            'keluhan' => 'Biaya terlalu tinggi dicancel user',
            'status' => 'batal',
            'tanggal_masuk' => now(),
            'tanggal_ambil' => null,
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->call('openAmbilBatalModal', $servis->id)
            ->assertSet('modalAmbilBatalOpen', true)
            ->set('catatanSerahTerima', 'Diambil langsung pelanggan')
            ->call('serahkanUnitBatal')
            ->assertHasNoErrors();

        $servis->refresh();
        $this->assertNotNull($servis->tanggal_ambil);
    }
}
