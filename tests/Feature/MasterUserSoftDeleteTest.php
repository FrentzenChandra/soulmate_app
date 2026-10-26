<?php

namespace Tests\Feature;

use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterUserSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_owner_can_soft_delete_non_owner_user(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staff = User::where('email', 'admin.pkp@soulmate.test')->first();

        $this->actingAs($owner);

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $staff->id)
            ->assertHasNoErrors()
            ->assertSee('berhasil dihapus');

        // Verify soft delete in database
        $this->assertSoftDeleted('users', [
            'id' => $staff->id,
            'email' => 'admin.pkp@soulmate.test',
        ]);

        // Verify user is not found in active query
        $this->assertNull(User::find($staff->id));
        // But exists in withTrashed
        $this->assertNotNull(User::withTrashed()->find($staff->id));
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $owner->id)
            ->assertSee('Anda tidak dapat menghapus akun yang sedang Anda gunakan');

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cannot_delete_last_active_owner(): void
    {
        // Keep only 1 owner
        $owners = User::role('Owner')->get();
        if ($owners->count() > 1) {
            foreach ($owners->skip(1) as $extraOwner) {
                $extraOwner->forceDelete();
            }
        }

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $lastOwner = User::role('Owner')->first();

        // Acting as superadmin who shouldn't be able to delete owner anyway
        $this->actingAs($superadmin);

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $lastOwner->id)
            ->assertSee('Anda tidak memiliki otoritas');

        $this->assertDatabaseHas('users', [
            'id' => $lastOwner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_superadmin_cannot_delete_user_from_different_branch(): void
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $adminSgl = User::where('email', 'admin.sgl@soulmate.test')->first();

        $this->actingAs($superadminPkp);

        Livewire::test(MasterUserIndex::class)
            ->call('deleteUser', $adminSgl->id)
            ->assertSee('Anda tidak memiliki otoritas');

        $this->assertDatabaseHas('users', [
            'id' => $adminSgl->id,
            'deleted_at' => null,
        ]);
    }

    public function test_soft_deleted_user_cannot_login(): void
    {
        $staff = User::where('email', 'admin.pkp@soulmate.test')->first();
        $staff->delete();

        $response = $this->post('/login', [
            'email' => 'admin.pkp@soulmate.test',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_index_page_only_shows_active_users_excluding_soft_deleted(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staff = User::where('email', 'admin.pkp@soulmate.test')->first();

        $this->actingAs($owner);

        // Before delete, staff is visible on index
        Livewire::test(MasterUserIndex::class)
            ->set('search', $staff->name)
            ->assertSee($staff->name);

        // Delete staff
        $staff->delete();

        // After soft-delete, staff is excluded from index
        Livewire::test(MasterUserIndex::class)
            ->set('search', $staff->name)
            ->assertDontSee($staff->name);
    }

    public function test_historical_transaction_relations_retain_soft_deleted_user_details(): void
    {
        $staff = User::where('email', 'admin.pkp@soulmate.test')->first();
        $cabang = Cabang::first();

        // Create transaction associated with staff
        $trx = \App\Models\TransaksiFull::create([
            'cabang_id' => $cabang->id,
            'user_id' => $staff->id,
            'no_transaksi' => 'INV-TEST-001',
            'tipe_transaksi' => 'penjualan',
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
            'nama_pelanggan' => 'Pelanggan Test',
            'tanggal' => now(),
            'total_modal' => 50000,
            'total_biaya' => 75000,
            'grand_total' => 75000,
            'total_laba' => 25000,
        ]);

        // Soft delete the staff
        $staff->delete();

        // Refresh transaction and ensure user relation is not null
        $trx->refresh();
        $this->assertNotNull($trx->user);
        $this->assertEquals($staff->name, $trx->user->name);
        $this->assertTrue($trx->user->trashed());
    }
}

