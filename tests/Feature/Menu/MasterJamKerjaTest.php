<?php

namespace Tests\Feature\Menu;

use App\Enums\ShiftType;
use App\Livewire\Master\JamKerja\Index as JamKerjaIndex;
use App\Models\Cabang;
use App\Models\JamKerja;
use App\Models\User;
use Database\Seeders\JamKerjaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterJamKerjaTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();
        $this->seed(JamKerjaSeeder::class);

        $this->cabang1 = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'alamat' => 'Jl. Jendral Sudirman No. 1',
            'no_wa' => '081234567891',
            'status' => 'aktif',
        ]);

        $this->cabang2 = Cabang::create([
            'kode_cabang' => 'CB-SGL',
            'nama_cabang' => 'Cabang Sungailiat',
            'alamat' => 'Jl. Muhidin No. 2',
            'no_wa' => '081234567892',
            'status' => 'aktif',
        ]);
    }

    public static function nonOwnerRolesProvider(): array
    {
        return [
            'Role Superadmin' => ['Superadmin'],
            'Role Admin' => ['Admin'],
            'Role Teknisi' => ['Teknisi'],
            'Role Bendahara' => ['Bendahara'],
            'Role Operasional' => ['Operasional'],
            'Role Magang' => ['Magang'],
        ];
    }

    // =========================================================================
    // 1. AKSES & OTORISASI
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('master.jam-kerja'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('nonOwnerRolesProvider')]
    public function test_non_owner_roles_cannot_access_master_jam_kerja(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('master.jam-kerja'));
        $response->assertForbidden();
    }

    public function test_owner_can_access_master_jam_kerja(): void
    {
        $this->actingAsRole('Owner');

        $response = $this->get(route('master.jam-kerja'));
        $response->assertOk();
    }

    // =========================================================================
    // 2. FUNGSIONALITAS CRUD & ATURAN SHIFT
    // =========================================================================

    public function test_owner_can_view_default_global_shifts(): void
    {
        $this->actingAsRole('Owner');

        Livewire::test(JamKerjaIndex::class)
            ->assertSee('Master Jam Masuk Pegawai')
            ->assertSee('Shift Pagi')
            ->assertSee('Shift Sore')
            ->assertSee('Default Global (Semua Cabang)');
    }

    public function test_owner_can_create_cabang_override_rule(): void
    {
        $this->actingAsRole('Owner');

        Livewire::test(JamKerjaIndex::class)
            ->call('openCreateModal')
            ->set('cabang_id', $this->cabang1->id)
            ->set('nama_shift', 'pagi')
            ->set('jam_mulai', '07:30')
            ->set('jam_masuk', '08:30')
            ->set('toleransi_menit', 5)
            ->set('jam_cutoff', '13:00')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('jam_kerjas', [
            'cabang_id' => $this->cabang1->id,
            'nama_shift' => 'pagi',
            'jam_mulai' => '07:30:00',
            'jam_masuk' => '08:30:00',
            'toleransi_menit' => 5,
        ]);
    }

    public function test_duplicate_cabang_and_shift_is_rejected(): void
    {
        $this->actingAsRole('Owner');

        // Rule default global untuk shift pagi sudah ada dari seeder
        Livewire::test(JamKerjaIndex::class)
            ->call('openCreateModal')
            ->set('cabang_id', null)
            ->set('nama_shift', 'pagi')
            ->set('jam_mulai', '07:00')
            ->set('jam_masuk', '08:00')
            ->set('toleransi_menit', 2)
            ->set('jam_cutoff', '13:00')
            ->call('save')
            ->assertHasErrors(['nama_shift']);
    }

    public function test_owner_can_edit_existing_shift_rule(): void
    {
        $this->actingAsRole('Owner');

        $pagiRule = JamKerja::whereNull('cabang_id')->where('nama_shift', 'pagi')->firstOrFail();

        Livewire::test(JamKerjaIndex::class)
            ->call('openEditModal', $pagiRule->id)
            ->assertSet('nama_shift', 'pagi')
            ->assertSet('toleransi_menit', 2)
            ->set('toleransi_menit', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('jam_kerjas', [
            'id' => $pagiRule->id,
            'toleransi_menit' => 3,
        ]);
    }

    public function test_owner_can_toggle_shift_status(): void
    {
        $this->actingAsRole('Owner');

        $soreRule = JamKerja::whereNull('cabang_id')->where('nama_shift', 'sore')->firstOrFail();
        $this->assertTrue($soreRule->is_aktif);

        Livewire::test(JamKerjaIndex::class)
            ->call('toggleStatus', $soreRule->id);

        $this->assertFalse($soreRule->fresh()->is_aktif);
    }

    public function test_owner_cannot_delete_global_default_rule(): void
    {
        $this->actingAsRole('Owner');

        $pagiRule = JamKerja::whereNull('cabang_id')->where('nama_shift', 'pagi')->firstOrFail();

        Livewire::test(JamKerjaIndex::class)
            ->call('deleteJamKerja', $pagiRule->id);

        $this->assertDatabaseHas('jam_kerjas', [
            'id' => $pagiRule->id,
        ]);
    }

    public function test_owner_can_delete_cabang_override_rule(): void
    {
        $this->actingAsRole('Owner');

        $override = JamKerja::create([
            'cabang_id' => $this->cabang1->id,
            'nama_shift' => ShiftType::Pagi,
            'jam_mulai' => '07:15:00',
            'jam_masuk' => '08:15:00',
            'toleransi_menit' => 5,
            'jam_cutoff' => '13:00:00',
            'is_aktif' => true,
        ]);

        Livewire::test(JamKerjaIndex::class)
            ->call('deleteJamKerja', $override->id);

        $this->assertDatabaseMissing('jam_kerjas', [
            'id' => $override->id,
        ]);
    }
}

