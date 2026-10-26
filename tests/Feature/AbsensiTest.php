<?php

namespace Tests\Feature;

use App\Livewire\Absensi\Index as AbsensiIndex;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\User;
use App\Models\WifiAbsen;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_access_absensi_page(): void
    {
        $this->get(route('absensi.index'))->assertRedirect(route('login'));
    }

    public function test_authorized_staff_can_access_absensi_page(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);
        $this->get(route('absensi.index'))->assertOk();

        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);
        $this->get(route('absensi.index'))->assertOk();

        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($teknisi);
        $this->get(route('absensi.index'))->assertOk();
    }

    public function test_jaringan_valid_logic_and_caching(): void
    {
        $service = app(AbsensiService::class);
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Register custom WiFi for user's branch
        WifiAbsen::create([
            'cabang_id' => $user->cabang_id,
            'nama' => 'WiFi Test Kantor',
            'ip_address' => '192.168.10.50',
            'is_aktif' => true,
        ]);

        // Valid branch IP
        $this->assertTrue($service->jaringanValid($user, '192.168.10.50'));

        // Localhost IP (seeded)
        $this->assertTrue($service->jaringanValid($user, '127.0.0.1'));

        // Invalid IP
        $this->assertFalse($service->jaringanValid($user, '10.0.0.99'));

        // Verify cache is set
        $cacheKey = AbsensiService::getCacheKey($user->cabang_id);
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_jaringan_valid_supports_ipv4_and_ipv6_wildcard_subnets(): void
    {
        $service = app(AbsensiService::class);
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // 1. Daftarkan wildcard subnet IPv4
        WifiAbsen::create([
            'cabang_id' => $user->cabang_id,
            'nama' => 'WiFi Kantor LAN Subnet',
            'ip_address' => '192.168.18.*',
            'is_aktif' => true,
        ]);
        AbsensiService::clearCache($user->cabang_id);

        // Laptop & HP staf di subnet yang sama harus valid
        $this->assertTrue($service->jaringanValid($user, '192.168.18.9'));
        $this->assertTrue($service->jaringanValid($user, '192.168.18.15'));
        $this->assertTrue($service->jaringanValid($user, '192.168.18.254'));

        // IP di luar subnet harus ditolak
        $this->assertFalse($service->jaringanValid($user, '192.168.1.9'));
        $this->assertFalse($service->jaringanValid($user, '10.0.0.5'));

        // 2. Daftarkan wildcard subnet IPv6 router kantor
        WifiAbsen::create([
            'cabang_id' => $user->cabang_id,
            'nama' => 'WiFi Kantor IPv6 Subnet',
            'ip_address' => '2404:8000:1036:ac2:*',
            'is_aktif' => true,
        ]);
        AbsensiService::clearCache($user->cabang_id);

        // Laptop & HP staf dengan prefix router IPv6 yang sama harus valid
        $this->assertTrue($service->jaringanValid($user, '2404:8000:1036:ac2:3688:b97b:d7f:cd94'));
        $this->assertTrue($service->jaringanValid($user, '2404:8000:1036:ac2:95b6:e3cd:47ed:e354'));

        // IPv6 dari router / ISP lain harus ditolak
        $this->assertFalse($service->jaringanValid($user, '2404:8000:9999:ffff:1111:2222:3333:4444'));
    }

    public function test_absen_service_creates_attendance_with_asia_jakarta_time(): void
    {
        $service = app(AbsensiService::class);
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $absen = $service->absen($user, '127.0.0.1', 'Mozilla/5.0 Testing');

        $this->assertInstanceOf(Absensi::class, $absen);
        $this->assertEquals($user->id, $absen->user_id);
        $this->assertEquals($user->cabang_id, $absen->cabang_id);
        $this->assertEquals(Carbon::now('Asia/Jakarta')->toDateString(), $absen->tanggal->toDateString());
        $this->assertEquals('127.0.0.1', $absen->ip_address);
    }

    public function test_absen_service_throws_exception_when_not_in_branch_wifi(): void
    {
        $service = app(AbsensiService::class);
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $this->expectException(\RuntimeException::class);
        $service->absen($user, '198.51.100.99', 'Fake Agent');
    }

    public function test_absen_service_returns_existing_record_if_already_clocked_in(): void
    {
        $service = app(AbsensiService::class);
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $first = $service->absen($user, '127.0.0.1');
        $second = $service->absen($user, '127.0.0.1');

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(1, Absensi::where('user_id', $user->id)->count());
    }

    public function test_livewire_absensi_flow_and_states(): void
    {
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($user);

        // State 1: Siap (using localhost 127.0.0.1)
        Livewire::test(AbsensiIndex::class)
            ->assertSet('statusState', 'siap')
            ->assertSee('Tekan untuk absen')
            ->assertSee('WiFi Kantor Terhubung')
            ->call('submitAbsen')
            ->assertSet('statusState', 'sudah_absen')
            ->assertSee('Sudah selesai absen');

        // Verify record in database
        $this->assertDatabaseHas('absensis', [
            'user_id' => $user->id,
            'cabang_id' => $user->cabang_id,
        ]);

        $record = Absensi::where('user_id', $user->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals(Carbon::now('Asia/Jakarta')->toDateString(), $record->tanggal->toDateString());
    }

    public function test_absensi_user_relation_works_with_trashed_user(): void
    {
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $service = app(AbsensiService::class);
        $absen = $service->absen($user, '127.0.0.1');

        // Soft delete the user
        $user->delete();

        // Fresh load absensi
        $loaded = Absensi::find($absen->id);
        $this->assertNotNull($loaded->user);
        $this->assertEquals($user->name, $loaded->user->name);
        $this->assertTrue($loaded->user->trashed());
    }

    public function test_device_cannot_be_used_by_two_different_users_on_same_day(): void
    {
        $service = app(AbsensiService::class);
        $user1 = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $user2 = User::where('email', 'admin.pkp@soulmate.test')->first();

        $deviceIdShared = 'device-uuid-test-12345';

        // User 1 clocks in with Device A -> Success
        $absen1 = $service->absen($user1, '127.0.0.1', null, $deviceIdShared);
        $this->assertInstanceOf(Absensi::class, $absen1);
        $this->assertEquals($deviceIdShared, $absen1->device_id);

        // User 2 attempts to clock in with the same Device A -> Rejected
        try {
            $service->absen($user2, '127.0.0.1', null, $deviceIdShared);
            $this->fail('Harusnya gagal karena perangkat sudah dipakai user lain hari ini.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Perangkat ini sudah digunakan untuk absensi', $e->getMessage());
        }

        // User 2 clocks in with a different Device B -> Success
        $deviceIdUser2 = 'device-uuid-test-67890';
        $absen2 = $service->absen($user2, '127.0.0.1', null, $deviceIdUser2);
        $this->assertInstanceOf(Absensi::class, $absen2);
        $this->assertEquals($deviceIdUser2, $absen2->device_id);
    }
}
