<?php

namespace Tests\Feature\Menu;

use App\Livewire\Servis\NonReal\Index as ServisNonRealIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use App\Models\ServisDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class ServisNonRealTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected Customer $customer;

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

        $this->cabang2 = Cabang::create([
            'nama_cabang' => 'Cabang Sungailiat',
            'kode_cabang' => 'CB-SGL',
            'alamat' => 'Jl. Jenderal Sudirman No. 88',
            'no_wa' => '081234567891',
            'status' => 'aktif',
        ]);

        $this->customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'CV Maju Bersama',
            'no_hp' => '081399887766',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('servis.non-real'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Admin has access' => ['Admin', 200],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
            'Owner has access via bypass' => ['Owner', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_servis_non_real_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('servis.non-real'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('servis.non_real.view', [], $this->cabang1);

        $this->get(route('servis.non-real'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('servis.non-real'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD SERVIS NON-REAL
    // =========================================================================

    public function test_authorized_role_can_create_servis_non_real(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(ServisNonRealIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalCreateOpen', true)
            ->set('tipeUnit', 'Epson L3210 (Nota Kantor)')
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('customTanggalMasuk', now()->format('Y-m-d\TH:i'))
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('servis_masuks', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'tipe_unit' => 'Epson L3210 (Nota Kantor)',
            'is_real' => 0,
        ]);
    }

    public function test_delete_action_allowed_only_for_superadmin_and_owner(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00030',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $admin->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus VivoBook Non Real',
            'keluhan' => 'Nota Khusus',
            'status' => 'selesai',
            'is_real' => false,
            'total_biaya' => 500000,
        ]);

        // 1. Admin DILARANG menghapus servis non real (403)
        Livewire::test(ServisNonRealIndex::class)
            ->call('confirmDelete', $servis->id)
            ->assertForbidden();

        // 2. Superadmin DIIZINKAN menghapus servis non real
        $this->actingAsRole('Superadmin', [], $this->cabang1);
        Livewire::test(ServisNonRealIndex::class)
            ->call('confirmDelete', $servis->id)
            ->assertSet('modalDeleteOpen', true)
            ->call('deleteServis')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('servis_masuks', [
            'id' => $servis->id,
        ]);
    }

    public function test_detail_modal_shows_servis_non_real_data(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00031',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $admin->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Rakitan Desain',
            'keluhan' => 'Servis Non Real',
            'status' => 'selesai',
            'is_real' => false,
            'total_biaya' => 750000,
        ]);

        Livewire::test(ServisNonRealIndex::class)
            ->call('openDetailModal', $servis->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('PC Rakitan Desain')
            ->assertSee('CV Maju Bersama');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_servis_non_real_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('servis.non-real'));
    }

    public function test_sidebar_does_not_display_servis_non_real_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Magang', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('servis.non-real'));
    }
}

