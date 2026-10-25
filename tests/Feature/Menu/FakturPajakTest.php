<?php

namespace Tests\Feature\Menu;

use App\Livewire\FakturPajak\Index as FakturPajakIndex;
use App\Models\Cabang;
use App\Models\FakturPajak;
use App\Models\MasterKodePajak;
use App\Models\MasterOrangPajak;
use App\Models\MasterPph;
use App\Models\MasterViaPajak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class FakturPajakTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected MasterViaPajak $viaPajak;
    protected MasterKodePajak $kodePajak;
    protected MasterPph $pph22;
    protected MasterOrangPajak $orangPajak;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Cabang Pangkalpinang',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Merdeka No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->viaPajak = MasterViaPajak::create([
            'nama' => 'E-Katalog',
            'is_aktif' => true,
        ]);

        $this->kodePajak = MasterKodePajak::create([
            'kode' => '010',
            'deskripsi' => 'Penyerahan kepada selain Pemungut PPN',
            'is_aktif' => true,
        ]);

        $this->pph22 = MasterPph::create([
            'nama_pph' => 'PPh 22 (1.5%)',
            'persentase' => 1.5,
            'is_aktif' => true,
        ]);

        $this->orangPajak = MasterOrangPajak::create([
            'nama' => 'Dinas Pendidikan Kota Pangkalpinang',
            'is_aktif' => true,
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('faktur-pajak.index'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Operasional has access' => ['Operasional', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_faktur_pajak_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('faktur-pajak.index'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('pajak.view', [], $this->cabang1);

        $this->get(route('faktur-pajak.index'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('faktur-pajak.index'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD FAKTUR PAJAK
    // =========================================================================

    public function test_authorized_user_can_create_faktur_pajak(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(FakturPajakIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalFormOpen', true)
            ->set('formNamaPajak', 'Dinas Pendidikan Kota Pangkalpinang')
            ->set('formBarang', 'Pengadaan 10 Unit Laptop Core i5')
            ->set('formMasterViaPajakId', $this->viaPajak->id)
            ->set('formViaPajak', $this->viaPajak->nama)
            ->set('formMasterKodePajakId', $this->kodePajak->id)
            ->set('formKodePajak', $this->kodePajak->kode)
            ->set('formMasterPphId', $this->pph22->id)
            ->set('formJenisPph', $this->pph22->nama_pph)
            ->set('formPphPersen', 1.5)
            ->set('formBruto', '100000000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('faktur_pajaks', [
            'cabang_id' => $this->cabang1->id,
            'nama_pajak_snapshot' => 'Dinas Pendidikan Kota Pangkalpinang',
            'barang_snapshot' => 'Pengadaan 10 Unit Laptop Core i5',
            'via_pajak_snapshot' => 'E-Katalog',
            'bruto' => 100000000,
        ]);
    }

    public function test_authorized_user_can_toggle_selesai_faktur_pajak(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $faktur = FakturPajak::create([
            'no_faktur' => 'FP-2026-0001',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'tanggal' => now(),
            'nama_pajak_snapshot' => 'Dinas Kesehatan Bangka',
            'barang_snapshot' => 'Pengadaan Tinta Printer',
            'via_pajak_snapshot' => 'E-Katalog',
            'jenis_pph_snapshot' => 'Non-PPh',
            'bruto' => 5000000,
            'dpp' => 4504504.50,
            'dpp_lain' => 4129129.13,
            'ppn_nominal' => 495495.50,
            'pph_nominal' => 0,
            'netto' => 4504504.50,
            'is_selesai' => false,
        ]);

        Livewire::test(FakturPajakIndex::class)
            ->call('toggleSelesai', $faktur->id);

        $this->assertTrue($faktur->fresh()->is_selesai);
    }

    public function test_delete_action_allowed_for_bendahara_but_forbidden_for_admin(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $faktur = FakturPajak::create([
            'no_faktur' => 'FP-2026-0002',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'tanggal' => now(),
            'nama_pajak_snapshot' => 'Bappeda Bangka',
            'barang_snapshot' => 'Maintenance PC Server',
            'via_pajak_snapshot' => 'E-Katalog',
            'jenis_pph_snapshot' => 'Non-PPh',
            'bruto' => 15000000,
            'dpp' => 13513513.51,
            'dpp_lain' => 12387387.39,
            'ppn_nominal' => 1486486.49,
            'pph_nominal' => 0,
            'netto' => 13513513.51,
            'is_selesai' => false,
        ]);

        // 1. Admin DILARANG menghapus faktur pajak (403)
        Livewire::test(FakturPajakIndex::class)
            ->call('delete', $faktur->id)
            ->assertForbidden();

        // 2. Bendahara DIIZINKAN menghapus faktur pajak
        $this->actingAsRole('Bendahara', [], $this->cabang1);
        Livewire::test(FakturPajakIndex::class)
            ->call('delete', $faktur->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('faktur_pajaks', [
            'id' => $faktur->id,
        ]);
    }

    public function test_detail_modal_shows_complete_faktur_data(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $faktur = FakturPajak::create([
            'no_faktur' => 'FP-2026-0003',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'tanggal' => now(),
            'nama_pajak_snapshot' => 'Diskominfo Babel',
            'barang_snapshot' => 'Pemasangan Switch Cisco',
            'via_pajak_snapshot' => 'E-Katalog',
            'jenis_pph_snapshot' => 'Non-PPh',
            'bruto' => 25000000,
            'dpp' => 22522522.52,
            'dpp_lain' => 20645645.64,
            'ppn_nominal' => 2477477.48,
            'pph_nominal' => 0,
            'netto' => 22522522.52,
            'is_selesai' => false,
        ]);

        Livewire::test(FakturPajakIndex::class)
            ->call('openDetail', $faktur->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('Diskominfo Babel')
            ->assertSee('Pemasangan Switch Cisco');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_faktur_pajak_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('faktur-pajak.index'));
    }

    public function test_sidebar_does_not_display_faktur_pajak_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('faktur-pajak.index'));
    }
}

