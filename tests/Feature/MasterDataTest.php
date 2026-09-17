<?php

namespace Tests\Feature;

use App\Livewire\Master\Customer\Index as MasterCustomerIndex;
use App\Livewire\Master\Ppn\Index as MasterPpnIndex;
use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_superadmin_can_access_user_management_and_create_staff_for_their_branch()
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $cabangPkp = Cabang::where('kode_cabang', 'CB-PKP')->first();

        $this->actingAs($superadminPkp);

        // Superadmin can view /master/users
        $response = $this->get(route('master.users'));
        $response->assertStatus(200);

        // Superadmin creates a new Teknisi in their branch
        Livewire::test(MasterUserIndex::class)
            ->set('name', 'Bambang Teknisi Baru')
            ->set('username', 'bambang_tek')
            ->set('email', 'bambang@soulmate.test')
            ->set('password', 'secret123')
            ->set('role_name', 'Teknisi')
            ->set('no_hp', '081233344455')
            ->call('save')
            ->assertHasNoErrors();

        $createdUser = User::where('email', 'bambang@soulmate.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($cabangPkp->id, $createdUser->cabang_id);
        $this->assertTrue($createdUser->hasRole('Teknisi'));
    }

    public function test_superadmin_cannot_create_owner_account()
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadminPkp);

        Livewire::test(MasterUserIndex::class)
            ->set('name', 'Penyusup Owner')
            ->set('username', 'penyusup')
            ->set('email', 'penyusup@soulmate.test')
            ->set('password', 'secret123')
            ->set('role_name', 'Owner')
            ->call('save')
            ->assertHasErrors(['role_name']);

        $this->assertNull(User::where('email', 'penyusup@soulmate.test')->first());
    }

    public function test_superadmin_cannot_edit_or_toggle_owner_or_other_branch_user()
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $superadminSgl = User::where('email', 'superadmin.sgl@soulmate.test')->first();

        $this->actingAs($superadminPkp);

        // Cannot toggle Owner
        Livewire::test(MasterUserIndex::class)
            ->call('toggleStatus', $owner->id)
            ->assertSee('Anda tidak memiliki otoritas');

        $this->assertEquals('aktif', $owner->fresh()->status);

        // Cannot toggle other branch Superadmin
        Livewire::test(MasterUserIndex::class)
            ->call('toggleStatus', $superadminSgl->id)
            ->assertSee('Anda tidak memiliki otoritas');
    }

    public function test_owner_can_create_user_with_any_role_and_assign_branch()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $cabangSgl = Cabang::where('kode_cabang', 'CB-SGL')->first();

        $this->actingAs($owner);

        Livewire::test(MasterUserIndex::class)
            ->set('name', 'Staff Admin SGL')
            ->set('username', 'admin_sgl_2')
            ->set('email', 'admin2.sgl@soulmate.test')
            ->set('password', 'secret123')
            ->set('role_name', 'Admin')
            ->set('cabang_id', $cabangSgl->id)
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'admin2.sgl@soulmate.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals($cabangSgl->id, $user->cabang_id);
        $this->assertTrue($user->hasRole('Admin'));
    }

    public function test_superadmin_can_view_and_filter_admin_users_in_their_branch()
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadminPkp);

        Livewire::test(MasterUserIndex::class)
            ->set('roleFilter', 'Admin')
            ->assertSee('Siti Admin PKP');
    }

    public function test_master_ppn_crud_and_protections()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();

        // Superadmin cannot access master.ppn
        $this->actingAs($superadmin);
        $this->get(route('master.ppn'))->assertStatus(403);

        // Owner can access and create PPN
        $this->actingAs($owner);
        $this->get(route('master.ppn'))->assertStatus(200);

        Livewire::test(MasterPpnIndex::class)
            ->set('nama', 'PPN Khusus 15%')
            ->set('persen', 15.00)
            ->set('is_aktif', true)
            ->call('save')
            ->assertHasNoErrors();

        $ppn = MasterPpn::where('nama', 'PPN Khusus 15%')->first();
        $this->assertNotNull($ppn);
        $this->assertEquals(15.00, (float) $ppn->persen);

        // Toggle status
        Livewire::test(MasterPpnIndex::class)
            ->call('toggleStatus', $ppn->id);

        $this->assertFalse((bool) $ppn->fresh()->is_aktif);

        // Delete PPN
        Livewire::test(MasterPpnIndex::class)
            ->call('deletePpn', $ppn->id);

        $this->assertNull(MasterPpn::find($ppn->id));
    }

    public function test_master_customer_crud_and_branch_scoping()
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $cabangPkp = Cabang::where('kode_cabang', 'CB-PKP')->first();

        $this->actingAs($superadminPkp);

        // Superadmin creates customer for PKP
        Livewire::test(MasterCustomerIndex::class)
            ->set('nama', 'Pelanggan Setia PKP')
            ->set('no_hp', '081987654321')
            ->call('save')
            ->assertHasNoErrors();

        $customer = Customer::where('nama', 'Pelanggan Setia PKP')->first();
        $this->assertNotNull($customer);
        $this->assertEquals($cabangPkp->id, $customer->cabang_id);
        $this->assertEquals('langganan', $customer->tipe);

        // Superadmin edits customer
        Livewire::test(MasterCustomerIndex::class)
            ->call('openEditModal', $customer->id)
            ->set('no_hp', '081999000111')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('081999000111', $customer->fresh()->no_hp);

        // Delete customer
        Livewire::test(MasterCustomerIndex::class)
            ->call('deleteCustomer', $customer->id);

        $this->assertNull(Customer::find($customer->id));
    }
}
