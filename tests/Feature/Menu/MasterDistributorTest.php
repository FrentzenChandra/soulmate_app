<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Distributor\Index as MasterDistributorIndex;
use App\Models\MasterDistributor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterDistributorTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin berhak akses' => ['Admin', 200],
            'Teknisi berhak akses' => ['Teknisi', 200],
            'Bendahara ditolak 403' => ['Bendahara', 403],
            'Operasional ditolak 403' => ['Operasional', 403],
            'Magang ditolak 403' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_master_distributor_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.distributor'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.distributor'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_non_authorized_dengan_direct_permission_bisa_akses(): void
    {
        $user = $this->actingAsRole('Bendahara');
        $user->givePermissionTo('master.distributor.view');

        $response = $this->get(route('master.distributor'));

        $response->assertStatus(200);
    }

    public function test_render_komponen_livewire_master_distributor(): void
    {
        $this->actingAsRole('Admin');

        MasterDistributor::create([
            'nama' => 'PT Nusantara Jaya Komputer',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterDistributorIndex::class)
            ->assertStatus(200)
            ->assertSee('PT Nusantara Jaya Komputer');
    }

    public function test_admin_dapat_menambah_distributor(): void
    {
        $this->actingAsRole('Admin');

        Livewire::test(MasterDistributorIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', false)
            ->set('nama', 'CV Mitra Sukses Mandiri')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('master_distributors', [
            'nama' => 'CV Mitra Sukses Mandiri',
            'is_aktif' => 1,
        ]);
    }

    public function test_validasi_nama_distributor_wajib_diisi(): void
    {
        $this->actingAsRole('Admin');

        Livewire::test(MasterDistributorIndex::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->call('save')
            ->assertHasErrors(['nama' => 'required']);
    }

    public function test_teknisi_ditolak_403_saat_mencoba_menambah_edit_atau_hapus(): void
    {
        $this->actingAsRole('Teknisi');

        $item = MasterDistributor::create([
            'nama' => 'Distributor Testing',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterDistributorIndex::class)
            ->call('openCreateModal')
            ->assertForbidden();

        Livewire::test(MasterDistributorIndex::class)
            ->call('edit', $item->id)
            ->assertForbidden();

        Livewire::test(MasterDistributorIndex::class)
            ->call('delete', $item->id)
            ->assertForbidden();
    }

    public function test_admin_dapat_mengedit_distributor(): void
    {
        $this->actingAsRole('Admin');

        $item = MasterDistributor::create([
            'nama' => 'PT Distributor Lama',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterDistributorIndex::class)
            ->call('edit', $item->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('nama', 'PT Distributor Lama')
            ->set('nama', 'PT Distributor Baru Diedit')
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('master_distributors', [
            'id' => $item->id,
            'nama' => 'PT Distributor Baru Diedit',
        ]);
    }

    public function test_admin_dapat_soft_delete_distributor(): void
    {
        $this->actingAsRole('Admin');

        $item = MasterDistributor::create([
            'nama' => 'Distributor Akan Dihapus',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterDistributorIndex::class)
            ->call('delete', $item->id)
            ->assertDispatched('swal:toast');

        $this->assertSoftDeleted('master_distributors', [
            'id' => $item->id,
        ]);
    }

    public function test_pencarian_multi_kata_dan_filter_status(): void
    {
        $this->actingAsRole('Admin');

        MasterDistributor::create([
            'nama' => 'PT Bintang Terang Komputer',
            'is_aktif' => true,
        ]);

        MasterDistributor::create([
            'nama' => 'CV Cahaya Abadi Elektronik',
            'is_aktif' => false,
        ]);

        // Search multi-word
        Livewire::test(MasterDistributorIndex::class)
            ->set('search', 'Bintang Terang')
            ->assertSee('PT Bintang Terang Komputer')
            ->assertDontSee('CV Cahaya Abadi Elektronik');

        // Filter status
        Livewire::test(MasterDistributorIndex::class)
            ->set('statusFilter', 'nonaktif')
            ->assertSee('CV Cahaya Abadi Elektronik')
            ->assertDontSee('PT Bintang Terang Komputer');
    }
}

