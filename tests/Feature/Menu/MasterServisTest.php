<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Servis\Index as MasterServisIndex;
use App\Models\MasterServis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterServisTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin berhak akses' => ['Admin', 200],
            'Teknisi berhak akses' => ['Teknisi', 200],
            'Magang berhak akses' => ['Magang', 200],
            'Bendahara ditolak 403' => ['Bendahara', 403],
            'Operasional ditolak 403' => ['Operasional', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_master_servis_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.servis'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.servis'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_non_authorized_dengan_direct_permission_bisa_akses(): void
    {
        $user = $this->actingAsRole('Bendahara');
        $user->givePermissionTo('master.servis.view');

        $response = $this->get(route('master.servis'));

        $response->assertStatus(200);
    }

    public function test_render_komponen_livewire_master_servis(): void
    {
        $this->actingAsRole('Teknisi');

        MasterServis::create([
            'kode_item' => 'JS-001',
            'nama_item' => 'Install Ulang Windows 11',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 0,
            'harga_jual' => 100000,
            'garansi_default_hari' => 14,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterServisIndex::class)
            ->assertStatus(200)
            ->assertSee('Install Ulang Windows 11')
            ->assertSee('JS-001');
    }

    public function test_superadmin_dapat_menambah_layanan_servis(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(MasterServisIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalFormOpen', true)
            ->assertSet('isEditMode', false)
            ->set('nama_item', 'Pembersihan Kipas & Thermal Paste')
            ->set('kategori_perangkat', 'laptop')
            ->set('harga_modal', 15000)
            ->set('harga_jual', 75000)
            ->set('garansi_default_hari', 7)
            ->call('save')
            ->assertSet('modalFormOpen', false);

        $this->assertDatabaseHas('master_servis', [
            'nama_item' => 'Pembersihan Kipas & Thermal Paste',
            'kategori_perangkat' => 'laptop',
            'harga_jual' => 75000,
            'jenis' => 'jasa',
        ]);
    }

    public function test_validasi_input_servis_wajib_diisi(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(MasterServisIndex::class)
            ->call('openCreateModal')
            ->set('nama_item', '')
            ->set('harga_jual', '')
            ->call('save')
            ->assertHasErrors(['nama_item', 'harga_jual']);
    }

    public function test_teknisi_ditolak_403_saat_mencoba_menambah_atau_mengedit_servis(): void
    {
        $this->actingAsRole('Teknisi');

        $item = MasterServis::create([
            'kode_item' => 'JS-TEST',
            'nama_item' => 'Servis Testing',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_jual' => 50000,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterServisIndex::class)
            ->call('openCreateModal')
            ->assertForbidden();

        Livewire::test(MasterServisIndex::class)
            ->call('editItem', $item->id)
            ->assertForbidden();

        Livewire::test(MasterServisIndex::class)
            ->call('confirmDelete', $item->id)
            ->assertForbidden();
    }

    public function test_superadmin_dapat_mengedit_layanan_servis(): void
    {
        $this->actingAsRole('Superadmin');

        $item = MasterServis::create([
            'kode_item' => 'JS-999',
            'nama_item' => 'Ganti Keyboard Original',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 100000,
            'harga_jual' => 200000,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterServisIndex::class)
            ->call('editItem', $item->id)
            ->assertSet('modalFormOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('nama_item', 'Ganti Keyboard Original')
            ->set('nama_item', 'Ganti Keyboard Original & Backlight')
            ->set('harga_jual', 250000)
            ->call('save')
            ->assertSet('modalFormOpen', false);

        $this->assertDatabaseHas('master_servis', [
            'id' => $item->id,
            'nama_item' => 'Ganti Keyboard Original & Backlight',
            'harga_jual' => 250000,
        ]);
    }

    public function test_superadmin_dapat_toggle_aktif_layanan_servis(): void
    {
        $this->actingAsRole('Superadmin');

        $item = MasterServis::create([
            'kode_item' => 'JS-TOGGLE',
            'nama_item' => 'Refill Tinta Printer',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'printer',
            'harga_jual' => 35000,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterServisIndex::class)
            ->call('toggleAktif', $item->id);

        $this->assertFalse($item->fresh()->is_aktif);
    }

    public function test_superadmin_dapat_menghapus_layanan_servis_tanpa_riwayat(): void
    {
        $this->actingAsRole('Superadmin');

        $item = MasterServis::create([
            'kode_item' => 'JS-DEL',
            'nama_item' => 'Servis Percobaan Hapus',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'komputer',
            'harga_jual' => 45000,
            'is_aktif' => true,
        ]);

        Livewire::test(MasterServisIndex::class)
            ->call('confirmDelete', $item->id)
            ->assertSet('modalDeleteOpen', true)
            ->call('deleteItem')
            ->assertSet('modalDeleteOpen', false);

        $this->assertDatabaseMissing('master_servis', [
            'id' => $item->id,
        ]);
    }

    public function test_pencarian_multi_kata_dan_filter_kategori(): void
    {
        $this->actingAsRole('Teknisi');

        MasterServis::create([
            'kode_item' => 'JS-LP-1',
            'nama_item' => 'Ganti Engsel Laptop Asus',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_jual' => 120000,
            'is_aktif' => true,
        ]);

        MasterServis::create([
            'kode_item' => 'JS-PR-1',
            'nama_item' => 'Reset Waste Ink Pad Epson',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'printer',
            'harga_jual' => 80000,
            'is_aktif' => true,
        ]);

        // Search multi-kata
        Livewire::test(MasterServisIndex::class)
            ->set('search', 'Engsel Laptop')
            ->assertSee('Ganti Engsel Laptop Asus')
            ->assertDontSee('Reset Waste Ink Pad Epson');

        // Filter kategori perangkat
        Livewire::test(MasterServisIndex::class)
            ->set('kategoriFilter', 'printer')
            ->assertSee('Reset Waste Ink Pad Epson')
            ->assertDontSee('Ganti Engsel Laptop Asus');
    }
}

