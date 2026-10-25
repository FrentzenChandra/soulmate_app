<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\WifiAbsen\Index as MasterWifiAbsenIndex;
use App\Models\Cabang;
use App\Models\WifiAbsen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterWifiAbsenTest extends TestCase
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
    public function test_akses_halaman_master_wifi_absen_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.wifi-absen'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.wifi-absen'));

        $response->assertRedirect(route('login'));
    }

    public function test_render_komponen_livewire_master_wifi_absen(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'WiFi Toko Lantai 1',
            'ip_address' => '192.168.1.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        Livewire::test(MasterWifiAbsenIndex::class)
            ->assertStatus(200)
            ->assertSee('WiFi Toko Lantai 1')
            ->assertSee('192.168.1.*');
    }

    public function test_superadmin_dapat_menambah_wifi_absen_di_cabangnya(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEdit', false)
            ->set('nama', 'WiFi Teknisi Lantai 2')
            ->set('ip_address', '192.168.2.*')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertSet('successMessage', 'Data WiFi absensi baru berhasil didaftarkan.');

        $this->assertDatabaseHas('wifi_absens', [
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'WiFi Teknisi Lantai 2',
            'ip_address' => '192.168.2.*',
            'is_aktif' => 1,
            'created_by' => $superadmin->id,
        ]);
    }

    public function test_validasi_input_wifi_wajib_diisi(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->set('ip_address', '')
            ->call('save')
            ->assertHasErrors(['nama', 'ip_address']);
    }

    public function test_superadmin_dapat_mengedit_wifi_absen_di_cabangnya(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $wifi = WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'WiFi Kasir Lama',
            'ip_address' => '192.168.10.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('openEditModal', $wifi->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEdit', true)
            ->assertSet('nama', 'WiFi Kasir Lama')
            ->set('nama', 'WiFi Kasir Baru & CS')
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertSet('successMessage', 'Data WiFi absensi berhasil diperbarui.');

        $this->assertDatabaseHas('wifi_absens', [
            'id' => $wifi->id,
            'nama' => 'WiFi Kasir Baru & CS',
        ]);
    }

    public function test_superadmin_tidak_bisa_edit_wifi_cabang_lain(): void
    {
        $superadmin1 = $this->actingAsRole('Superadmin');

        $cabang2 = Cabang::create([
            'kode_cabang' => 'CB-2',
            'nama_cabang' => 'Cabang Dua',
            'alamat' => 'Jl. Lain',
            'no_wa' => '0812345678',
            'status' => 'aktif',
        ]);

        $wifiCabang2 = WifiAbsen::create([
            'cabang_id' => $cabang2->id,
            'nama' => 'WiFi Cabang 2',
            'ip_address' => '10.0.0.*',
            'is_aktif' => true,
            'created_by' => $superadmin1->id,
        ]);

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('openEditModal', $wifiCabang2->id)
            ->assertForbidden();
    }

    public function test_superadmin_dapat_toggle_status_wifi(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $wifi = WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'WiFi Toggle',
            'ip_address' => '192.168.5.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('toggleStatus', $wifi->id)
            ->assertSet('successMessage', 'Status WiFi berhasil diubah.');

        $this->assertFalse((bool) $wifi->fresh()->is_aktif);
    }

    public function test_superadmin_dapat_menghapus_wifi_absen(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $wifi = WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'WiFi Hapus',
            'ip_address' => '192.168.99.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        Livewire::test(MasterWifiAbsenIndex::class)
            ->call('delete', $wifi->id)
            ->assertSet('successMessage', 'Data WiFi absensi berhasil dihapus.');

        $this->assertDatabaseMissing('wifi_absens', [
            'id' => $wifi->id,
        ]);
    }

    public function test_pencarian_multi_kata_wifi_absen(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Fiber Optic Router Utama',
            'ip_address' => '192.168.100.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        WifiAbsen::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Backup Modem 4G',
            'ip_address' => '192.168.200.*',
            'is_aktif' => true,
            'created_by' => $superadmin->id,
        ]);

        // Search multi-kata
        Livewire::test(MasterWifiAbsenIndex::class)
            ->set('search', 'Fiber Optic')
            ->assertSee('Fiber Optic Router Utama')
            ->assertDontSee('Backup Modem 4G');
    }
}

