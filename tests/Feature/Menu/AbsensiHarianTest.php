<?php

namespace Tests\Feature\Menu;

use App\Livewire\Absensi\Index as AbsensiIndex;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\WifiAbsen;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class AbsensiHarianTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();

        $this->cabang = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'alamat' => 'Jl. Jendral Sudirman No. 1',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);
    }

    /**
     * Data provider role yang memiliki akses ke Absensi Harian.
     */
    public static function authorizedRolesProvider(): array
    {
        return [
            'Role Owner' => ['Owner'],
            'Role Superadmin' => ['Superadmin'],
            'Role Teknisi' => ['Teknisi'],
            'Role Admin' => ['Admin'],
            'Role Bendahara' => ['Bendahara'],
            'Role Operasional' => ['Operasional'],
        ];
    }

    /**
     * Data provider role yang TIDAK memiliki akses ke Absensi Harian.
     */
    public static function unauthorizedRolesProvider(): array
    {
        return [
            'Role Magang' => ['Magang'],
        ];
    }

    // =========================================================================
    // TAHAP 2A: AKSE & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('absensi.index'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('authorizedRolesProvider')]
    public function test_authorized_roles_can_access_absensi_page(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang);

        $response = $this->get(route('absensi.index'));
        $response->assertOk();
    }

    #[DataProvider('unauthorizedRolesProvider')]
    public function test_unauthorized_roles_receive_403_forbidden(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang);

        $response = $this->get(route('absensi.index'));
        $response->assertForbidden();
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang);

        $response = $this->get(route('absensi.index'));
        $response->assertOk();
    }

    public function test_user_with_multi_role_inherits_highest_permission(): void
    {
        // Magang tidak punya akses, tapi jika digabung dengan Operasional maka berhak akses
        $this->actingAsUserWithRoles(['Magang', 'Operasional'], [], $this->cabang);

        $response = $this->get(route('absensi.index'));
        $response->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL & VALIDASI ABSENSI
    // =========================================================================

    public function test_staff_cannot_submit_when_ip_is_not_whitelisted(): void
    {
        $user = $this->actingAsRole('Teknisi', [], $this->cabang);

        // Tidak ada IP WiFi yang didaftarkan untuk cabang ini
        Livewire::test(AbsensiIndex::class)
            ->set('clientIp', '192.168.100.99')
            ->assertSet('isWifiValid', false)
            ->assertSet('statusState', 'bukan_wifi')
            ->call('submitAbsen')
            ->assertSee('tidak terhubung ke jaringan WiFi cabang yang terdaftar');

        $this->assertDatabaseMissing('absensis', [
            'user_id' => $user->id,
        ]);
    }

    public function test_staff_can_successfully_submit_attendance_on_whitelisted_ip(): void
    {
        $user = $this->actingAsRole('Admin', [], $this->cabang);

        // Whitelist IP WiFi toko
        WifiAbsen::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'WiFi Kasir Soulmate',
            'ip_address' => '127.0.0.1',
            'is_aktif' => true,
        ]);

        Livewire::test(AbsensiIndex::class)
            ->set('clientIp', '127.0.0.1')
            ->set('deviceId', 'device-test-uuid-123')
            ->call('submitAbsen')
            ->assertSet('statusState', 'sudah_absen')
            ->assertSee('Absensi berhasil dicatat');

        $this->assertDatabaseHas('absensis', [
            'user_id' => $user->id,
            'cabang_id' => $this->cabang->id,
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_staff_cannot_double_submit_on_same_day(): void
    {
        $user = $this->actingAsRole('Operasional', [], $this->cabang);

        // Buat absensi yang sudah ada hari ini
        Absensi::create([
            'user_id' => $user->id,
            'cabang_id' => $this->cabang->id,
            'tanggal' => Carbon::now('Asia/Jakarta')->toDateString(),
            'waktu_absen' => Carbon::now('Asia/Jakarta'),
            'ip_address' => '127.0.0.1',
            'device_id' => 'device-test-1',
        ]);

        Livewire::test(AbsensiIndex::class)
            ->assertSet('statusState', 'sudah_absen')
            ->assertSet('isWifiValid', true)
            ->assertDontSee('Absen Sekarang');
    }

    public function test_user_without_submit_permission_is_blocked_on_action(): void
    {
        // Berikan izin view saja tanpa izin submit
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang);

        WifiAbsen::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'WiFi Kantor',
            'ip_address' => '127.0.0.1',
            'is_aktif' => true,
        ]);

        Livewire::test(AbsensiIndex::class)
            ->set('clientIp', '127.0.0.1')
            ->call('submitAbsen')
            ->assertStatus(403);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND & SIDEBAR VISIBILITY ASSERTIONS
    // =========================================================================

    public function test_sidebar_displays_absensi_menu_only_for_authorized_roles(): void
    {
        // 1. Authorized role: Teknisi -> Sidebar harus menampilkan Absensi
        $this->actingAsRole('Teknisi', [], $this->cabang);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('absensi.index'));

        // 2. Unauthorized role: Magang -> Sidebar DILARANG menampilkan link Absensi
        $this->actingAsRole('Magang', [], $this->cabang);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('absensi.index'));
    }
}
