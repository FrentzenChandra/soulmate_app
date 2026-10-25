<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Cabang\Index as MasterCabangIndex;
use App\Livewire\Master\KodePajak\Index as MasterKodePajakIndex;
use App\Livewire\Master\Pph\Index as MasterPphIndex;
use App\Livewire\Master\Ppn\Index as MasterPpnIndex;
use App\Livewire\Master\ViaPajak\Index as MasterViaPajakIndex;
use App\Livewire\Pengaturan\HakAkses\Index as HakAksesIndex;
use App\Models\Cabang;
use App\Models\MasterKodePajak;
use App\Models\MasterPph;
use App\Models\MasterPpn;
use App\Models\MasterViaPajak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class OwnerHqModulesTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin ditolak 403' => ['Superadmin', 403],
            'Admin ditolak 403' => ['Admin', 403],
            'Teknisi ditolak 403' => ['Teknisi', 403],
            'Bendahara ditolak 403' => ['Bendahara', 403],
            'Operasional ditolak 403' => ['Operasional', 403],
            'Magang ditolak 403' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_owner_hq_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $this->get(route('master.cabang'))->assertStatus($expectedStatus);
        $this->get(route('master.ppn'))->assertStatus($expectedStatus);
        $this->get(route('master.hak-akses'))->assertStatus($expectedStatus);
        $this->get(route('master.via-pajak'))->assertStatus($expectedStatus);
        $this->get(route('master.kode-pajak'))->assertStatus($expectedStatus);
        $this->get(route('master.pph'))->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $this->get(route('master.cabang'))->assertRedirect(route('login'));
        $this->get(route('master.ppn'))->assertRedirect(route('login'));
        $this->get(route('master.hak-akses'))->assertRedirect(route('login'));
        $this->get(route('master.via-pajak'))->assertRedirect(route('login'));
        $this->get(route('master.kode-pajak'))->assertRedirect(route('login'));
        $this->get(route('master.pph'))->assertRedirect(route('login'));
    }

    public function test_crud_master_cabang_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        // Create
        Livewire::test(MasterCabangIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('kode_cabang', 'CB-BALI')
            ->set('nama_cabang', 'Cabang Denpasar')
            ->set('alamat', 'Jl. Teuku Umar No. 88')
            ->set('no_wa', '08123456789')
            ->set('status', 'aktif')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('cabangs', [
            'kode_cabang' => 'CB-BALI',
            'nama_cabang' => 'Cabang Denpasar',
        ]);

        $cabang = Cabang::where('kode_cabang', 'CB-BALI')->first();

        // Edit
        Livewire::test(MasterCabangIndex::class)
            ->call('openEditModal', $cabang->id)
            ->set('nama_cabang', 'Cabang Denpasar Bali')
            ->call('save');

        $this->assertDatabaseHas('cabangs', [
            'id' => $cabang->id,
            'nama_cabang' => 'Cabang Denpasar Bali',
        ]);

        // Toggle Status
        Livewire::test(MasterCabangIndex::class)
            ->call('toggleStatus', $cabang->id);

        $this->assertEquals('nonaktif', $cabang->fresh()->status);
    }

    public function test_crud_master_ppn_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        // Create
        Livewire::test(MasterPpnIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama', 'PPN 12 Persen')
            ->set('persen', 12.00)
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_ppns', [
            'nama' => 'PPN 12 Persen',
            'persen' => 12.00,
        ]);

        $ppn = MasterPpn::where('nama', 'PPN 12 Persen')->first();

        // Edit
        Livewire::test(MasterPpnIndex::class)
            ->call('openEditModal', $ppn->id)
            ->set('nama', 'PPN 12 Persen Resmi')
            ->call('save');

        $this->assertDatabaseHas('master_ppns', [
            'id' => $ppn->id,
            'nama' => 'PPN 12 Persen Resmi',
        ]);

        // Toggle Status
        Livewire::test(MasterPpnIndex::class)
            ->call('toggleStatus', $ppn->id);

        $this->assertFalse((bool) $ppn->fresh()->is_aktif);
    }

    public function test_pengaturan_hak_akses_role_matrix_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        Livewire::test(HakAksesIndex::class)
            ->call('selectRole', 'Teknisi')
            ->assertSet('selectedRole', 'Teknisi')
            ->call('selectAll')
            ->call('save')
            ->assertDispatched('swal:success');

        $teknisiRole = Role::where('name', 'Teknisi')->first();
        $this->assertGreaterThan(10, $teknisiRole->permissions()->count());

        // Reset to default
        Livewire::test(HakAksesIndex::class)
            ->call('selectRole', 'Teknisi')
            ->call('resetToDefault')
            ->call('save')
            ->assertDispatched('swal:success');
    }

    public function test_crud_master_via_pajak_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        // Create
        Livewire::test(MasterViaPajakIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama', 'DJP Online CoreTax')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_via_pajaks', [
            'nama' => 'DJP Online CoreTax',
        ]);

        $item = MasterViaPajak::where('nama', 'DJP Online CoreTax')->first();

        // Edit
        Livewire::test(MasterViaPajakIndex::class)
            ->call('edit', $item->id)
            ->set('nama', 'DJP Online CoreTax v2')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_via_pajaks', [
            'id' => $item->id,
            'nama' => 'DJP Online CoreTax v2',
        ]);

        // Delete
        Livewire::test(MasterViaPajakIndex::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('master_via_pajaks', [
            'id' => $item->id,
        ]);
    }

    public function test_crud_master_kode_pajak_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        // Create
        Livewire::test(MasterKodePajakIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('kode', '070')
            ->set('keterangan', 'Penyerahan BKP/JKP Tertentu Tidak Dipungut')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_kode_pajaks', [
            'kode' => '070',
        ]);

        $item = MasterKodePajak::where('kode', '070')->first();

        // Edit
        Livewire::test(MasterKodePajakIndex::class)
            ->call('edit', $item->id)
            ->set('keterangan', 'Penyerahan Bebas PPN')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_kode_pajaks', [
            'id' => $item->id,
            'keterangan' => 'Penyerahan Bebas PPN',
        ]);

        // Delete
        Livewire::test(MasterKodePajakIndex::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('master_kode_pajaks', [
            'id' => $item->id,
        ]);
    }

    public function test_crud_master_pph_oleh_owner(): void
    {
        $this->actingAsRole('Owner');

        // Create
        Livewire::test(MasterPphIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama_pph', 'PPh 23 Jasa Teknik')
            ->set('persentase', '2.00')
            ->set('deskripsi', 'Pemotongan atas jasa teknik 2%')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_pphs', [
            'nama_pph' => 'PPh 23 Jasa Teknik',
            'persentase' => '2.00',
        ]);

        $item = MasterPph::where('nama_pph', 'PPh 23 Jasa Teknik')->first();

        // Edit
        Livewire::test(MasterPphIndex::class)
            ->call('edit', $item->id)
            ->set('nama_pph', 'PPh 23 Jasa Servis & Teknik')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('master_pphs', [
            'id' => $item->id,
            'nama_pph' => 'PPh 23 Jasa Servis & Teknik',
        ]);

        // Delete
        Livewire::test(MasterPphIndex::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('master_pphs', [
            'id' => $item->id,
        ]);
    }
}

