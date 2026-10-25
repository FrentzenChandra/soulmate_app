<?php

namespace Tests\Feature\Menu;

use App\Livewire\Cabang\Stok\Index as CabangStokIndex;
use App\Models\Cabang;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class CabangStokTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Produk $produk1;

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

        $this->produk1 = Produk::create([
            'nama_produk' => 'RAM Team Elite 8GB DDR4 3200MHz',
            'barcode' => 'TED48G3200',
            'harga_beli' => 240000,
            'harga_jual' => 310000,
            'status' => 'aktif',
        ]);

        Stok::create([
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 10,
            'min_stok' => 3,
            'is_aktif' => true,
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('cabang.stok'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Operasional has access' => ['Operasional', 200],
            'Magang has access' => ['Magang', 200],
            'Owner has access via bypass' => ['Owner', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_cabang_stok_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('cabang.stok'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('cabang.stok.view', [], $this->cabang1);

        $this->get(route('cabang.stok'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('cabang.stok'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL STOCK OPNAME & ADJUSTMENT
    // =========================================================================

    public function test_superadmin_can_adjust_cabang_stock(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(CabangStokIndex::class)
            ->call('openAdjustModal', $this->produk1->id)
            ->assertSet('modalAdjustOpen', true)
            ->set('newStock', 15)
            ->set('catatan', 'Penambahan stok dari kiriman distributor')
            ->call('saveAdjustment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stoks', [
            'produk_id' => $this->produk1->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 15,
        ]);
    }

    public function test_admin_blocked_from_adjusting_stock(): void
    {
        // Admin hanya berhak view, tidak berhak cabang.stok.adjust
        $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(CabangStokIndex::class)
            ->call('openAdjustModal', $this->produk1->id)
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_cabang_stok_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('cabang.stok'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('cabang.stok'));
    }
}

