<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Pegawai\Index as MasterPegawaiIndex;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterPegawaiTest extends TestCase
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
    public function test_akses_halaman_master_pegawai_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.pegawai'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.pegawai'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_non_authorized_dengan_direct_permission_bisa_akses(): void
    {
        $user = $this->actingAsRole('Admin');
        $user->givePermissionTo('master.pegawai.view');

        $response = $this->get(route('master.pegawai'));

        $response->assertStatus(200);
    }

    public function test_render_komponen_livewire_master_pegawai(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Agus Pratama',
            'role' => 'Teknisi Laptop',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterPegawaiIndex::class)
            ->assertStatus(200)
            ->assertSee('Agus Pratama')
            ->assertSee('Teknisi Laptop');
    }

    public function test_superadmin_dapat_menambah_pegawai_baru(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', false)
            ->set('nama', 'Doni Kurniawan')
            ->set('bagian', 'Teknisi Printer')
            ->call('simpanPegawai')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Doni Kurniawan',
            'role' => 'Teknisi Printer',
            'is_aktif' => 1,
        ]);
    }

    public function test_superadmin_dapat_menambah_pegawai_dengan_custom_bagian(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Siti Marketing')
            ->set('bagian', '__custom__')
            ->set('customBagian', 'Digital Marketing')
            ->call('simpanPegawai')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Siti Marketing',
            'role' => 'Digital Marketing',
        ]);
    }

    public function test_validasi_input_pegawai_wajib_diisi(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->call('simpanPegawai')
            ->assertHasErrors(['nama' => 'required']);
    }

    public function test_superadmin_dapat_mengedit_pegawai(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $pegawai = Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Rahmat Hidayat',
            'role' => 'Bendahara',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openEditModal', $pegawai->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('nama', 'Rahmat Hidayat')
            ->set('nama', 'Rahmat Hidayat Senior')
            ->set('bagian', 'Admin Pajak')
            ->call('simpanPegawai')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('pegawais', [
            'id' => $pegawai->id,
            'nama' => 'Rahmat Hidayat Senior',
            'role' => 'Admin Pajak',
        ]);
    }

    public function test_superadmin_dapat_toggle_status_pegawai(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $pegawai = Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Pegawai Toggle',
            'role' => 'Teknisi Komputer',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('toggleStatus', $pegawai->id);

        $this->assertFalse((bool) $pegawai->fresh()->is_aktif);
    }

    public function test_superadmin_dapat_menghapus_pegawai(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $pegawai = Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Pegawai Akan Dihapus',
            'role' => 'Admin Stok',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterPegawaiIndex::class)
            ->call('openDeleteModal', $pegawai->id, $pegawai->nama)
            ->assertSet('modalDeleteOpen', true)
            ->call('konfirmasiHapus')
            ->assertSet('modalDeleteOpen', false);

        $this->assertDatabaseMissing('pegawais', [
            'id' => $pegawai->id,
        ]);
    }

    public function test_pencarian_multi_kata_dan_filter_bagian(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Bayu Wicaksono',
            'role' => 'Teknisi Laptop',
            'is_aktif' => true,
        ]);

        Pegawai::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Citra Kirana',
            'role' => 'Admin Pajak',
            'is_aktif' => true,
        ]);

        // Search multi-kata
        Livewire::test(MasterPegawaiIndex::class)
            ->set('search', 'Bayu Wicaksono')
            ->assertSee('Bayu Wicaksono')
            ->assertDontSee('Citra Kirana');

        // Filter bagian
        Livewire::test(MasterPegawaiIndex::class)
            ->set('bagianFilter', 'Admin Pajak')
            ->assertSee('Citra Kirana')
            ->assertDontSee('Bayu Wicaksono');
    }
}

