<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterUserTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin ditolak 403' => ['Admin', 403],
            'Teknisi ditolak 403' => ['Teknisi', 403],
            'Bendahara ditolak 403' => ['Bendahara', 403],
            'Operasional ditolak 403' => ['Operasional', 403],
            'Magang ditolak 403' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_master_user_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.users'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.users'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_non_authorized_dengan_direct_permission_bisa_akses(): void
    {
        $user = $this->actingAsRole('Admin');
        $user->givePermissionTo('master.users.view');

        $response = $this->get(route('master.users'));

        $response->assertStatus(200);
    }

    public function test_render_komponen_livewire_master_user(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $staff = User::create([
            'name' => 'Staf Cabang Testing',
            'username' => 'staf_testing',
            'email' => 'staf_testing@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $superadmin->cabang_id,
            'status' => 'aktif',
        ]);
        $staff->assignRole('Teknisi');

        Livewire::test(MasterUserIndex::class)
            ->assertStatus(200)
            ->assertSee('Staf Cabang Testing')
            ->assertSee('staf_testing');
    }

    public function test_superadmin_dapat_menambah_user_di_cabangnya(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Livewire::test(MasterUserIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', false)
            ->set('name', 'Budi Teknisi Baru')
            ->set('username', 'buditeknisi')
            ->set('email', 'buditeknisi@soulmate.test')
            ->set('password', 'password123')
            ->set('role_name', 'Teknisi')
            ->set('status', 'aktif')
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Teknisi Baru',
            'username' => 'buditeknisi',
            'email' => 'buditeknisi@soulmate.test',
            'cabang_id' => $superadmin->cabang_id,
        ]);

        $createdUser = User::where('username', 'buditeknisi')->first();
        $this->assertTrue($createdUser->hasRole('Teknisi'));
    }

    public function test_validasi_input_user_wajib_diisi(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(MasterUserIndex::class)
            ->call('openCreateModal')
            ->set('name', '')
            ->set('username', '')
            ->set('email', '')
            ->set('password', '')
            ->call('save')
            ->assertHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_superadmin_dapat_mengedit_user_di_cabangnya(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $staff = User::create([
            'name' => 'Staf Lama',
            'username' => 'staflama',
            'email' => 'staflama@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $superadmin->cabang_id,
            'status' => 'aktif',
        ]);
        $staff->assignRole('Admin');

        Livewire::test(MasterUserIndex::class)
            ->call('openEditModal', $staff->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('name', 'Staf Lama')
            ->set('name', 'Staf Baru Diedit')
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Staf Baru Diedit',
        ]);
    }

    public function test_superadmin_tidak_bisa_edit_user_cabang_lain(): void
    {
        $superadmin1 = $this->actingAsRole('Superadmin');

        $cabang2 = Cabang::create([
            'kode_cabang' => 'CB-2',
            'nama_cabang' => 'Cabang Dua',
            'alamat' => 'Jl. Lain',
            'no_wa' => '0812345678',
            'status' => 'aktif',
        ]);

        $staffCabang2 = User::create([
            'name' => 'Staf Cabang Dua',
            'username' => 'stafcb2',
            'email' => 'stafcb2@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang2->id,
            'status' => 'aktif',
        ]);
        $staffCabang2->assignRole('Admin');

        Livewire::test(MasterUserIndex::class)
            ->call('openEditModal', $staffCabang2->id)
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:error');
    }

    public function test_proteksi_self_lockout_tidak_bisa_hapus_akun_sendiri(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $superadmin->id)
            ->assertDispatched('swal:warning');

        $this->assertDatabaseHas('users', [
            'id' => $superadmin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_dapat_soft_delete_staf_cabangnya(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $staff = User::create([
            'name' => 'Staf Akan Dihapus',
            'username' => 'stafhapus',
            'email' => 'stafhapus@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $superadmin->cabang_id,
            'status' => 'aktif',
        ]);
        $staff->assignRole('Teknisi');

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $staff->id)
            ->assertDispatched('swal:success');

        $this->assertSoftDeleted('users', [
            'id' => $staff->id,
        ]);
    }

    public function test_toggle_status_user_aktif_dan_nonaktif(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $staff = User::create([
            'name' => 'Staf Toggle',
            'username' => 'staftoggle',
            'email' => 'staftoggle@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $superadmin->cabang_id,
            'status' => 'aktif',
        ]);
        $staff->assignRole('Teknisi');

        Livewire::test(MasterUserIndex::class)
            ->call('toggleStatus', $staff->id)
            ->assertDispatched('swal:success');

        $this->assertEquals('nonaktif', $staff->fresh()->status);
    }
}

