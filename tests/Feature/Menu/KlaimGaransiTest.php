<?php

namespace Tests\Feature\Menu;

use App\Livewire\KlaimGaransi\Index as KlaimGaransiIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KlaimGaransi;
use App\Models\MasterDistributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class KlaimGaransiTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected Customer $customer;
    protected MasterDistributor $distributor;

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
            'nama' => 'Rian Hidayat',
            'no_hp' => '081233445566',
        ]);

        $this->distributor = MasterDistributor::create([
            'nama' => 'PT Synnex Metrodata',
            'is_aktif' => true,
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('klaim-garansi.index'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Admin has access' => ['Admin', 200],
            'Operasional has access' => ['Operasional', 200],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Magang is forbidden' => ['Magang', 403],
            'Owner has access via bypass' => ['Owner', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_klaim_garansi_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('klaim-garansi.index'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('klaim_garansi.view', [], $this->cabang1);

        $this->get(route('klaim-garansi.index'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('klaim-garansi.index'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD KLAIM GARANSI
    // =========================================================================

    public function test_authorized_role_can_create_klaim_garansi(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalFormOpen', true)
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('selectedCustomerNama', $this->customer->nama)
            ->set('selectedCustomerKontak', $this->customer->no_hp)
            ->set('formBarang', 'Motherboard ASUS Prime B450M-A')
            ->set('formKeluhan', 'Port PCIe x16 tidak detek VGA card')
            ->set('formMasterDistributorId', $this->distributor->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('klaim_garansis', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'barang' => 'Motherboard ASUS Prime B450M-A',
            'keluhan' => 'Port PCIe x16 tidak detek VGA card',
            'master_distributor_id' => $this->distributor->id,
        ]);
    }

    public function test_authorized_user_can_toggle_selesai_klaim(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $klaim = KlaimGaransi::create([
            'no_klaim' => 'KG-CB-PKP-261025-0001',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'customer_nama' => $this->customer->nama,
            'customer_kontak' => $this->customer->no_hp,
            'barang' => 'RAM Corsair Vengeance 8GB DDR4',
            'keluhan' => 'Bluescreen Memory Management',
            'tanggal_masuk' => now()->toDateString(),
            'master_distributor_id' => $this->distributor->id,
            'distributor_nama' => $this->distributor->nama,
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('toggleSelesai', $klaim->id);

        $this->assertNotNull($klaim->fresh()->tanggal_selesai_klaim);
    }

    public function test_delete_action_allowed_for_admin_but_forbidden_for_teknisi(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $klaim = KlaimGaransi::create([
            'no_klaim' => 'KG-CB-PKP-261025-0002',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'customer_nama' => $this->customer->nama,
            'barang' => 'SSD Kingston 240GB',
            'keluhan' => 'Bad sector',
            'tanggal_masuk' => now()->toDateString(),
        ]);

        // 1. Teknisi DILARANG menghapus data klaim garansi (403)
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        Livewire::test(KlaimGaransiIndex::class)
            ->call('confirmDelete', $klaim->id, $klaim->no_klaim)
            ->assertForbidden();

        // 2. Admin DIIZINKAN menghapus data klaim garansi
        $this->actingAsRole('Admin', [], $this->cabang1);
        Livewire::test(KlaimGaransiIndex::class)
            ->call('confirmDelete', $klaim->id, $klaim->no_klaim)
            ->assertSet('modalDeleteOpen', true)
            ->call('deleteKlaimDirect', $klaim->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('klaim_garansis', [
            'id' => $klaim->id,
        ]);
    }

    public function test_detail_modal_shows_complete_klaim_data(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $klaim = KlaimGaransi::create([
            'no_klaim' => 'KG-CB-PKP-261025-0003',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'customer_nama' => $this->customer->nama,
            'barang' => 'VGA GTX 1660 Super',
            'keluhan' => 'Artefak garis-garis',
            'tanggal_masuk' => now()->toDateString(),
        ]);

        Livewire::test(KlaimGaransiIndex::class)
            ->call('openDetailModal', $klaim->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('VGA GTX 1660 Super')
            ->assertSee('Rian Hidayat');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_klaim_garansi_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('klaim-garansi.index'));
    }

    public function test_sidebar_does_not_display_klaim_garansi_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Magang', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('klaim-garansi.index'));
    }
}

