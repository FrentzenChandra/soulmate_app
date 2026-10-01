<?php

namespace Tests\Feature;

use App\Livewire\Master\Pegawai\Index as MasterPegawaiIndex;
use App\Livewire\Transaksi\Full as TransaksiFullComponent;
use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TransaksiFull;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterPegawaiTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner']);
        Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Teknisi']);

        $this->cabang = Cabang::create([
            'nama_cabang' => 'Cabang Utama',
            'kode_cabang' => 'PST',
            'alamat' => 'Jl. Merdeka No. 1',
            'status' => 'aktif',
        ]);

        $this->owner = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->admin = User::factory()->create([
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_can_access_master_pegawai_page(): void
    {
        $this->actingAs($this->owner)
            ->get(route('master.pegawai'))
            ->assertStatus(200)
            ->assertSee('Master Pegawai')
            ->assertSee('Bagian');
    }

    public function test_can_create_pegawai_with_default_bagian(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Budi Teknisi')
            ->set('bagian', 'Teknisi Laptop')
            ->call('simpanPegawai')
            ->assertHasNoErrors()
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'nama' => 'Budi Teknisi',
            'role' => 'Teknisi Laptop',
            'cabang_id' => $this->cabang->id,
            'is_aktif' => true,
        ]);
    }

    public function test_can_create_pegawai_with_custom_new_bagian_dynamically(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Doni Jaringan')
            ->set('bagian', '__custom__')
            ->set('customBagian', 'Teknisi Jaringan')
            ->call('simpanPegawai')
            ->assertHasNoErrors()
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'nama' => 'Doni Jaringan',
            'role' => 'Teknisi Jaringan',
            'cabang_id' => $this->cabang->id,
            'is_aktif' => true,
        ]);

        // Next time, 'Teknisi Jaringan' must appear in distinct bagian options
        Livewire::test(MasterPegawaiIndex::class)
            ->assertSee('Teknisi Jaringan');
    }

    public function test_can_update_pegawai_bagian(): void
    {
        $pegawai = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Andi Pajak',
            'role' => 'Admin Stok',
            'is_aktif' => true,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openEditModal', $pegawai->id)
            ->set('nama', 'Andi Pajak Updated')
            ->set('bagian', 'Admin Pajak')
            ->call('simpanPegawai')
            ->assertHasNoErrors()
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'id' => $pegawai->id,
            'nama' => 'Andi Pajak Updated',
            'role' => 'Admin Pajak',
        ]);
    }

    public function test_can_filter_pegawai_by_bagian(): void
    {
        Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Staf Bendahara 1',
            'role' => 'Bendahara',
            'is_aktif' => true,
        ]);
        Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Staf Konten 1',
            'role' => 'Admin Konten',
            'is_aktif' => true,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(MasterPegawaiIndex::class)
            ->set('bagianFilter', 'Bendahara')
            ->assertSee('Staf Bendahara 1')
            ->assertDontSee('Staf Konten 1');
    }

    public function test_can_toggle_pegawai_status(): void
    {
        $pegawai = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Doni Teknisi',
            'role' => 'Teknisi Printer',
            'is_aktif' => true,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('toggleStatus', $pegawai->id);

        $this->assertDatabaseHas('pegawais', [
            'id' => $pegawai->id,
            'is_aktif' => false,
        ]);
    }

    public function test_transaksi_full_saves_with_pegawai_id(): void
    {
        $pegawai = Pegawai::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Siti Bendahara',
            'role' => 'Bendahara',
            'is_aktif' => true,
        ]);

        $this->actingAs($this->owner);

        Livewire::test(TransaksiFullComponent::class)
            ->call('openCreateModal')
            ->set('formNamaPelanggan', 'Pelanggan Tes')
            ->set('formPegawaiId', $pegawai->id)
            ->set('items', [
                [
                    'produk_id' => null,
                    'nama_item' => 'Jasa Install Windows',
                    'qty' => 1,
                    'harga_modal' => 20000,
                    'harga_jual' => 100000,
                    'subtotal_modal' => 20000,
                    'subtotal_jual' => 100000,
                    'laba' => 80000,
                ]
            ])
            ->call('simpanTransaksi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi_fulls', [
            'nama_pelanggan' => 'Pelanggan Tes',
            'pegawai_id' => $pegawai->id,
            'total_jual' => 100000,
        ]);
    }
}
