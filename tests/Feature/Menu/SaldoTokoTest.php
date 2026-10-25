<?php

namespace Tests\Feature\Menu;

use App\Livewire\Saldo\Index as SaldoIndex;
use App\Models\Cabang;
use App\Models\SaldoRekening;
use App\Models\SumberSaldo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class SaldoTokoTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected SumberSaldo $sumberPribadi;

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

        $this->sumberPribadi = SumberSaldo::firstOrCreate(
            ['nama' => 'PRIBADI'],
            ['deskripsi' => 'Rekening Tabungan & Pribadi']
        );
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (EKSKLUSIF BENDAHARA, SUPERADMIN, OWNER)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('saldo.index'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Admin is forbidden' => ['Admin', 403],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_saldo_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('saldo.index'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('saldo.view', [], $this->cabang1);

        $this->get(route('saldo.index'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('saldo.index'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD REKENING & QUICK SALDO
    // =========================================================================

    public function test_bendahara_can_create_rekening_saldo(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);

        Livewire::test(SaldoIndex::class)
            ->call('openCreateRekening', $this->sumberPribadi->id)
            ->assertSet('showRekeningModal', true)
            ->set('nama_bank', 'BCA Operasional Toko')
            ->set('atas_nama', 'Frentzen')
            ->set('nomor_rekening', '0881234567')
            ->set('saldo', 25000000)
            ->set('keterangan', 'Kas Utama BCA')
            ->call('saveRekening')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('saldo_rekenings', [
            'sumber_saldo_id' => $this->sumberPribadi->id,
            'nama_bank' => 'BCA Operasional Toko',
            'atas_nama' => 'Frentzen',
            'nomor_rekening' => '0881234567',
            'saldo' => 25000000,
        ]);
    }

    public function test_bendahara_can_quick_update_saldo(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);

        $rekening = SaldoRekening::create([
            'sumber_saldo_id' => $this->sumberPribadi->id,
            'nama_bank' => 'Mandiri Bisnis',
            'saldo' => 10000000,
        ]);

        Livewire::test(SaldoIndex::class)
            ->call('openQuickSaldo', $rekening->id)
            ->assertSet('showQuickSaldoModal', true)
            ->set('quickSaldo', 15000000)
            ->call('saveQuickSaldo')
            ->assertHasNoErrors();

        $this->assertEquals(15000000, $rekening->fresh()->saldo);
    }

    public function test_bendahara_can_delete_rekening_saldo(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);

        $rekening = SaldoRekening::create([
            'sumber_saldo_id' => $this->sumberPribadi->id,
            'nama_bank' => 'BNI Cadangan',
            'saldo' => 5000000,
        ]);

        Livewire::test(SaldoIndex::class)
            ->call('confirmDeleteRekening', $rekening->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteConfirmed')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('saldo_rekenings', [
            'id' => $rekening->id,
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_saldo_for_authorized_roles(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('saldo.index'));

        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('saldo.index'));
    }

    public function test_sidebar_does_not_display_saldo_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('saldo.index'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('saldo.index'));
    }
}

