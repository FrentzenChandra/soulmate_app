<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Master\Cabang\Index;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OwnerMasterTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $superadmin;

    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);

        $this->cabang = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'alamat' => 'Jl. Sudirman No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'username' => 'owner',
            'email' => 'owner@soulmate.test',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);
        $this->owner->syncRoles([$ownerRole]);

        $this->superadmin = User::create([
            'name' => 'Admin PKP',
            'username' => 'admin_pkp',
            'email' => 'admin.pkp@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->superadmin->syncRoles([$superadminRole]);
    }

    public function test_owner_can_access_dashboard_and_master_routes()
    {
        $this->actingAs($this->owner);

        $responseDashboard = $this->get('/dashboard');
        $responseDashboard->assertStatus(200);

        $responseCabang = $this->get('/master/cabang');
        $responseCabang->assertStatus(200);

        $responseUsers = $this->get('/master/users');
        $responseUsers->assertStatus(200);
    }

    public function test_superadmin_cannot_access_master_routes()
    {
        $this->actingAs($this->superadmin);

        $responseCabang = $this->get('/master/cabang');
        $responseCabang->assertStatus(403);

        $responseUsers = $this->get('/master/users');
        $responseUsers->assertStatus(403);
    }

    public function test_owner_can_create_new_cabang()
    {
        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->set('kode_cabang', 'CB-SGL')
            ->set('nama_cabang', 'Cabang Sungailiat')
            ->set('alamat', 'Jl. Muhidin No. 5')
            ->set('no_wa', '081299887766')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cabangs', [
            'kode_cabang' => 'CB-SGL',
            'nama_cabang' => 'Cabang Sungailiat',
        ]);
    }

    public function test_owner_can_create_new_owner_and_superadmin()
    {
        $this->actingAs($this->owner);

        // 1. Create New Owner
        Livewire::test(\App\Livewire\Master\User\Index::class)
            ->call('openCreateModal')
            ->set('role_name', 'Owner')
            ->set('name', 'Owner Kedua')
            ->set('username', 'owner2')
            ->set('email', 'owner2@soulmate.test')
            ->set('password', 'password123')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'owner2@soulmate.test',
            'username' => 'owner2',
            'cabang_id' => null,
        ]);

        // 2. Create Superadmin assigned to branch
        Livewire::test(\App\Livewire\Master\User\Index::class)
            ->call('openCreateModal')
            ->set('role_name', 'Superadmin')
            ->set('cabang_id', $this->cabang->id)
            ->set('name', 'Super Admin Baru')
            ->set('username', 'superadmin_baru')
            ->set('email', 'adminbaru@soulmate.test')
            ->set('password', 'password123')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@soulmate.test',
            'cabang_id' => $this->cabang->id,
        ]);
    }

    public function test_owner_cannot_deactivate_self()
    {
        $this->actingAs($this->owner);

        Livewire::test(\App\Livewire\Master\User\Index::class)
            ->call('toggleStatus', $this->owner->id);

        $this->assertEquals('aktif', $this->owner->fresh()->status);
    }

    public function test_user_can_login_via_livewire()
    {
        Livewire::test(Login::class)
            ->set('identity', 'owner')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->owner);
    }

    public function test_owner_can_edit_user_details()
    {
        $this->actingAs($this->owner);

        Livewire::test(\App\Livewire\Master\User\Index::class)
            ->call('openEditModal', $this->superadmin->id)
            ->set('name', 'Admin PKP Updated')
            ->set('no_hp', '089911223344')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->superadmin->id,
            'name' => 'Admin PKP Updated',
            'no_hp' => '089911223344',
        ]);
    }

    public function test_column_sorting_on_user_and_cabang_tables()
    {
        $this->actingAs($this->owner);

        // 1. Master User sorting
        Livewire::test(\App\Livewire\Master\User\Index::class)
            ->call('sortBy', 'username')
            ->assertSet('sortField', 'username')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'cabang_id')
            ->assertSet('sortField', 'cabang_id')
            ->call('sortBy', 'no_hp')
            ->assertSet('sortField', 'no_hp')
            ->call('sortBy', 'status')
            ->assertSet('sortField', 'status');

        // 2. Master Cabang sorting
        Livewire::test(Index::class)
            ->call('sortBy', 'nama_cabang')
            ->assertSet('sortField', 'nama_cabang')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'alamat')
            ->assertSet('sortField', 'alamat')
            ->call('sortBy', 'no_wa')
            ->assertSet('sortField', 'no_wa')
            ->call('sortBy', 'status')
            ->assertSet('sortField', 'status');
    }
}
