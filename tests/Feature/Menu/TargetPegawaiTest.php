<?php

namespace Tests\Feature\Menu;

use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class TargetPegawaiTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;

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
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (RULE 18: SUPERADMIN & OWNER ONLY)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('target.bulanan'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Admin is forbidden' => ['Admin', 403],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_target_bulanan_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('target.bulanan'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('target.bulanan.view', [], $this->cabang1);

        $this->get(route('target.bulanan'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('target.bulanan'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL ATUR TARGET TAHUNAN DIVISI (RULE 18 STANDARD)
    // =========================================================================

    public function test_superadmin_can_set_target_tahunan_divisi(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(TargetBulananIndex::class)
            ->call('openSetTargetTahunanDivisi')
            ->assertSet('modalSetTargetTahunanDivisiOpen', true)
            ->set('formTahunTargetDivisi', 2026)
            ->set('formTargetTahunanLaptop', 150)
            ->set('formTargetTahunanPrinter', 100)
            ->set('formTargetTahunanKomputer', 80)
            ->set('formTargetTahunanAdmin', 200)
            ->call('simpanTargetTahunanDivisi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('target_divisis', [
            'cabang_id' => $this->cabang1->id,
            'tahun' => 2026,
            'divisi' => 'laptop',
            'target_bulanan' => 150,
        ]);

        $this->assertDatabaseHas('target_divisis', [
            'cabang_id' => $this->cabang1->id,
            'tahun' => 2026,
            'divisi' => 'admin',
            'target_bulanan' => 200,
        ]);
    }

    public function test_unauthorized_user_blocked_from_setting_target(): void
    {
        // User dengan izin view saja tanpa target.bulanan.edit
        $this->actingAsUserWithPermission('target.bulanan.view', [], $this->cabang1);

        Livewire::test(TargetBulananIndex::class)
            ->call('openSetTargetTahunanDivisi')
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR (MOTIVATION SLOGAN)
    // =========================================================================

    public function test_target_bulanan_page_displays_motivational_slogan(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(TargetBulananIndex::class)
            ->assertSee('Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!');
    }

    public function test_sidebar_displays_target_bulanan_for_superadmin_and_owner(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('target.bulanan'));

        $this->actingAsRole('Owner');
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('target.bulanan'));
    }

    public function test_sidebar_does_not_display_target_bulanan_for_admin_and_teknisi(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('target.bulanan'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('target.bulanan'));
    }
}

