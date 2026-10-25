<?php

namespace Tests\Feature\Menu;

use App\Livewire\KasKeluar\Index as KasKeluarIndex;
use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\SumberDana;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class KasKeluarTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected SumberDana $sumberDana;
    protected KategoriKasKeluar $kategoriKas;

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

        $this->sumberDana = SumberDana::create([
            'nama' => 'Kas Toko / Laci',
            'is_aktif' => true,
        ]);

        $this->kategoriKas = KategoriKasKeluar::create([
            'nama' => 'Konsumsi & Operasional Harian',
            'is_aktif' => true,
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (EKSKLUSIF BENDAHARA, SUPERADMIN, OWNER)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('kas-keluar'))
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
    public function test_role_access_to_kas_keluar_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('kas-keluar'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('kas.keluar.view', [], $this->cabang1);

        $this->get(route('kas-keluar'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('kas-keluar'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD KAS KELUAR
    // =========================================================================

    public function test_bendahara_can_create_kas_keluar(): void
    {
        $bendahara = $this->actingAsRole('Bendahara', [], $this->cabang1);

        Livewire::test(KasKeluarIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('keterangan', 'Beli Kertas HVS A4 & Kopi Staf')
            ->set('nominal', 75000)
            ->set('sumber_dana_id', $this->sumberDana->id)
            ->set('kategori_kas_keluar_id', $this->kategoriKas->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kas_keluars', [
            'cabang_id' => $this->cabang1->id,
            'keterangan' => 'Beli Kertas HVS A4 & Kopi Staf',
            'nominal' => 75000,
            'sumber_dana_id' => $this->sumberDana->id,
            'kategori_kas_keluar_id' => $this->kategoriKas->id,
        ]);
    }

    public function test_bendahara_can_edit_kas_keluar(): void
    {
        $bendahara = $this->actingAsRole('Bendahara', [], $this->cabang1);

        $kas = KasKeluar::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $bendahara->id,
            'tanggal' => now(),
            'keterangan' => 'Beli Lakban Coklat',
            'nominal' => 15000,
            'sumber_dana_id' => $this->sumberDana->id,
            'kategori_kas_keluar_id' => $this->kategoriKas->id,
        ]);

        Livewire::test(KasKeluarIndex::class)
            ->call('openEditModal', $kas->id)
            ->assertSet('modalOpen', true)
            ->set('nominal', 20000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(20000, $kas->fresh()->nominal);
    }

    public function test_bendahara_can_delete_kas_keluar(): void
    {
        $bendahara = $this->actingAsRole('Bendahara', [], $this->cabang1);

        $kas = KasKeluar::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $bendahara->id,
            'tanggal' => now(),
            'keterangan' => 'Salah Input Kas Keluar',
            'nominal' => 50000,
            'sumber_dana_id' => $this->sumberDana->id,
            'kategori_kas_keluar_id' => $this->kategoriKas->id,
        ]);

        Livewire::test(KasKeluarIndex::class)
            ->call('delete', $kas->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('kas_keluars', [
            'id' => $kas->id,
        ]);
    }

    public function test_unauthorized_user_blocked_from_delete(): void
    {
        $bendahara = $this->actingAsRole('Bendahara', [], $this->cabang1);

        $kas = KasKeluar::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $bendahara->id,
            'tanggal' => now(),
            'keterangan' => 'Biaya Listrik',
            'nominal' => 250000,
            'sumber_dana_id' => $this->sumberDana->id,
            'kategori_kas_keluar_id' => $this->kategoriKas->id,
        ]);

        // User hanya punya hak view tanpa hak delete
        $this->actingAsUserWithPermission('kas.keluar.view', [], $this->cabang1);

        Livewire::test(KasKeluarIndex::class)
            ->call('delete', $kas->id)
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_kas_keluar_for_authorized_roles(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('kas-keluar'));

        $this->actingAsRole('Superadmin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('kas-keluar'));
    }

    public function test_sidebar_does_not_display_kas_keluar_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('kas-keluar'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('kas-keluar'));
    }
}

