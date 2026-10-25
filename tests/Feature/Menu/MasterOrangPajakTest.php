<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\OrangPajak\Index as MasterOrangPajakIndex;
use App\Models\MasterOrangPajak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterOrangPajakTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin berhak akses' => ['Admin', 200],
            'Bendahara berhak akses' => ['Bendahara', 200],
            'Operasional berhak akses' => ['Operasional', 200],
            'Teknisi ditolak 403' => ['Teknisi', 403],
            'Magang ditolak 403' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_master_orang_pajak_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.orang-pajak'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.orang-pajak'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_non_authorized_dengan_direct_permission_bisa_akses(): void
    {
        $user = $this->actingAsRole('Teknisi');
        $user->givePermissionTo('master.orang_pajak.view');

        $response = $this->get(route('master.orang-pajak'));

        $response->assertStatus(200);
    }

    public function test_render_komponen_livewire_master_orang_pajak(): void
    {
        $this->actingAsRole('Admin');

        MasterOrangPajak::create([
            'nama' => 'PT Sumber Rejeki Abadi',
            'npwp' => '01.234.567.8-901.000',
            'kontak' => '08123456789',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterOrangPajakIndex::class)
            ->assertStatus(200)
            ->assertSee('PT Sumber Rejeki Abadi')
            ->assertSee('01.234.567.8-901.000');
    }

    public function test_tambah_orang_pajak_berhasil(): void
    {
        $this->actingAsRole('Admin');

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', false)
            ->set('nama', 'CV Mitra Sukses Jaya')
            ->set('npwp', '02.345.678.9-012.000')
            ->set('kontak', '08987654321')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('master_orang_pajaks', [
            'nama' => 'CV Mitra Sukses Jaya',
            'npwp' => '02.345.678.9-012.000',
            'kontak' => '08987654321',
            'is_aktif' => 1,
        ]);
    }

    public function test_validasi_nama_wajib_diisi(): void
    {
        $this->actingAsRole('Admin');

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->call('save')
            ->assertHasErrors(['nama' => 'required']);
    }

    public function test_role_admin_ditolak_saat_mencoba_edit_dan_delete(): void
    {
        $this->actingAsRole('Admin');

        $item = MasterOrangPajak::create([
            'nama' => 'PT Restricted Edit',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('edit', $item->id)
            ->assertForbidden();

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('delete', $item->id)
            ->assertForbidden();
    }

    public function test_edit_orang_pajak_berhasil_oleh_bendahara(): void
    {
        $this->actingAsRole('Bendahara');

        $item = MasterOrangPajak::create([
            'nama' => 'CV Lama Perkasa',
            'npwp' => '11.111.111.1-111.000',
            'kontak' => '0811111111',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('edit', $item->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('nama', 'CV Lama Perkasa')
            ->set('nama', 'CV Perkasa Mandiri Sejahtera')
            ->set('kontak', '0822222222')
            ->call('save')
            ->assertSet('modalOpen', false)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('master_orang_pajaks', [
            'id' => $item->id,
            'nama' => 'CV Perkasa Mandiri Sejahtera',
            'kontak' => '0822222222',
        ]);
    }

    public function test_hapus_orang_pajak_berhasil_oleh_superadmin(): void
    {
        $this->actingAsRole('Superadmin');

        $item = MasterOrangPajak::create([
            'nama' => 'PT Akan Dihapus',
            'npwp' => '99.999.999.9-999.000',
            'kontak' => '0899999999',
            'is_aktif' => true,
        ]);

        Livewire::test(MasterOrangPajakIndex::class)
            ->call('delete', $item->id)
            ->assertDispatched('swal:toast');

        $this->assertDatabaseMissing('master_orang_pajaks', [
            'id' => $item->id,
        ]);
    }

    public function test_pencarian_multi_kata_dan_filter_status(): void
    {
        $this->actingAsRole('Admin');

        MasterOrangPajak::create([
            'nama' => 'PT Alfa Romeo Motor',
            'npwp' => '12.345.678.9-100.000',
            'kontak' => '08123',
            'is_aktif' => true,
        ]);

        MasterOrangPajak::create([
            'nama' => 'CV Beta Farma Medika',
            'npwp' => '98.765.432.1-200.000',
            'kontak' => '08456',
            'is_aktif' => false,
        ]);

        // Search tokenized multi-word
        Livewire::test(MasterOrangPajakIndex::class)
            ->set('search', 'Alfa Romeo')
            ->assertSee('PT Alfa Romeo Motor')
            ->assertDontSee('CV Beta Farma Medika');

        // Filter status aktif
        Livewire::test(MasterOrangPajakIndex::class)
            ->set('statusFilter', 'nonaktif')
            ->assertSee('CV Beta Farma Medika')
            ->assertDontSee('PT Alfa Romeo Motor');
    }
}
