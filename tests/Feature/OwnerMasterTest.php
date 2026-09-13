<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Livewire\Master\Cabang\Index;
use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Models\Cabang;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerMasterTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->owner = User::where('email', 'owner@soulmate.test')->first();
        $this->superadmin = User::where('email', 'superadmin@soulmate.test')->first();
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

    public function test_superadmin_cannot_access_owner_only_routes()
    {
        $this->actingAs($this->superadmin);

        $responseCabang = $this->get('/master/cabang');
        $responseCabang->assertStatus(403);

        $responsePpn = $this->get('/master/ppn');
        $responsePpn->assertStatus(403);

        // Superadmin CAN access /master/users for staff management
        $responseUsers = $this->get('/master/users');
        $responseUsers->assertStatus(200);
    }

    public function test_owner_can_create_new_cabang()
    {
        $this->actingAs($this->owner);

        Livewire::test(Index::class)
            ->call('openCreateModal')
            ->set('kode_cabang', 'CB-BEL')
            ->set('nama_cabang', 'Cabang Belinyu')
            ->set('alamat', 'Jl. Jenderal Sudirman No. 12')
            ->set('no_telp', '0718-12345')
            ->set('no_wa', '081234567890')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cabangs', [
            'kode_cabang' => 'CB-BEL',
            'nama_cabang' => 'Cabang Belinyu',
        ]);
    }

    public function test_owner_can_create_new_owner_and_superadmin()
    {
        $this->actingAs($this->owner);
        $cabang = Cabang::first();

        // 1. Create Superadmin
        Livewire::test(MasterUserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Cabang Baru')
            ->set('username', 'admin_baru')
            ->set('email', 'adminbaru@soulmate.test')
            ->set('password', 'password123')
            ->set('role_name', 'Superadmin')
            ->set('cabang_id', $cabang->id)
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@soulmate.test',
            'username' => 'admin_baru',
            'cabang_id' => $cabang->id,
        ]);

        $createdSuperadmin = User::where('email', 'adminbaru@soulmate.test')->first();
        $this->assertTrue($createdSuperadmin->hasRole('Superadmin'));

        // 2. Create Owner
        Livewire::test(MasterUserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'Owner Kedua')
            ->set('username', 'owner_kedua')
            ->set('email', 'owner2@soulmate.test')
            ->set('password', 'password123')
            ->set('role_name', 'Owner')
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'owner2@soulmate.test',
            'username' => 'owner_kedua',
            'cabang_id' => null,
        ]);

        $createdOwner = User::where('email', 'owner2@soulmate.test')->first();
        $this->assertTrue($createdOwner->hasRole('Owner'));
    }

    public function test_owner_cannot_deactivate_self()
    {
        $this->actingAs($this->owner);

        Livewire::test(MasterUserIndex::class)
            ->call('toggleStatus', $this->owner->id)
            ->assertSee('tidak dapat menonaktifkan');

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

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();

        Livewire::test(MasterUserIndex::class)
            ->call('openEditModal', $superadmin->id)
            ->set('name', 'Superadmin PKP Updated')
            ->set('no_hp', '089988776655')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('Superadmin PKP Updated', $superadmin->fresh()->name);
        $this->assertEquals('089988776655', $superadmin->fresh()->no_hp);
    }

    public function test_column_sorting_on_user_and_cabang_tables()
    {
        $this->actingAs($this->owner);

        // Test sorting on Cabang
        Livewire::test(Index::class)
            ->call('sortBy', 'nama_cabang')
            ->assertSet('sortField', 'nama_cabang')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'nama_cabang')
            ->assertSet('sortDirection', 'desc');

        // Test sorting on Users
        Livewire::test(MasterUserIndex::class)
            ->call('sortBy', 'email')
            ->assertSet('sortField', 'email')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'email')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_owner_can_upload_user_photo_to_storage()
    {
        Storage::fake('public');
        $this->actingAs($this->owner);

        $file = UploadedFile::fake()->image('profile.jpg', 300, 300);

        Livewire::test(MasterUserIndex::class)
            ->call('openCreateModal')
            ->set('name', 'User With Photo')
            ->set('username', 'user_photo')
            ->set('email', 'userphoto@soulmate.test')
            ->set('password', 'password123')
            ->set('role_name', 'Superadmin')
            ->set('cabang_id', Cabang::first()->id)
            ->set('foto', $file)
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'userphoto@soulmate.test')->first();
        $this->assertNotNull($user->foto_url);
        $this->assertTrue(str_starts_with($user->foto_url, 'storage/users/'));

        // Verify file was written to disk
        $relativePath = str_replace('storage/', '', $user->foto_url);
        Storage::disk('public')->assertExists($relativePath);
    }
}
