<?php

namespace Tests\Feature;

use App\Livewire\Master\WifiAbsen\Index as WifiAbsenIndex;
use App\Models\Cabang;
use App\Models\User;
use App\Models\WifiAbsen;
use App\Services\AbsensiService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class WifiAbsenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_access_wifi_absen(): void
    {
        $this->get(route('master.wifi-absen'))->assertRedirect(route('login'));
    }

    public function test_staff_roles_cannot_access_wifi_absen(): void
    {
        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($teknisi);
        $this->get(route('master.wifi-absen'))->assertStatus(403);

        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);
        $this->get(route('master.wifi-absen'))->assertStatus(403);
    }

    public function test_superadmin_can_access_and_is_locked_to_their_branch(): void
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $cabangSgl = Cabang::where('kode_cabang', 'CB-SGL')->first();

        $this->actingAs($superadminPkp);

        Livewire::test(WifiAbsenIndex::class)
            ->assertOk()
            ->assertDontSee('Semua Cabang') // Obfuscated
            ->call('openCreateModal')
            ->assertSet('cabang_id', $superadminPkp->cabang_id);
    }

    public function test_owner_can_manage_all_branches(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        Livewire::test(WifiAbsenIndex::class)
            ->assertOk()
            ->assertSee('Semua Cabang');
    }

    public function test_can_create_wifi_and_cache_is_cleared(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        // Pre-fill cache
        $cacheKey = AbsensiService::getCacheKey($superadmin->cabang_id);
        Cache::put($cacheKey, ['127.0.0.1'], 300);
        $this->assertTrue(Cache::has($cacheKey));

        Livewire::test(WifiAbsenIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Router Utama Toko')
            ->set('ip_address', '192.168.1.100')
            ->set('is_aktif', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('wifi_absens', [
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Router Utama Toko',
            'ip_address' => '192.168.1.100',
        ]);

        // Verify cache was invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_validation_prevents_duplicate_ip_in_same_branch(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Existing Router',
            'ip_address' => '192.168.1.1',
            'is_aktif' => true,
        ]);

        Livewire::test(WifiAbsenIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'New Router Duplicate')
            ->set('ip_address', '192.168.1.1')
            ->call('save')
            ->assertHasErrors(['ip_address']);
    }

    public function test_can_toggle_wifi_status(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $wifi = WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Toggle Router',
            'ip_address' => '192.168.1.250',
            'is_aktif' => true,
        ]);

        Livewire::test(WifiAbsenIndex::class)
            ->call('toggleStatus', $wifi->id);

        $this->assertFalse($wifi->fresh()->is_aktif);
    }

    public function test_can_delete_wifi_and_cache_is_cleared(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $wifi = WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'To Delete',
            'ip_address' => '192.168.1.251',
            'is_aktif' => true,
        ]);

        $cacheKey = AbsensiService::getCacheKey($superadmin->cabang_id);
        Cache::put($cacheKey, ['127.0.0.1'], 300);

        Livewire::test(WifiAbsenIndex::class)
            ->call('delete', $wifi->id);

        $this->assertDatabaseMissing('wifi_absens', ['id' => $wifi->id]);
        $this->assertFalse(Cache::has($cacheKey));
    }
}

