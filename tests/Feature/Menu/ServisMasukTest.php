<?php

namespace Tests\Feature\Menu;

use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class ServisMasukTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();

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
            'alamat' => 'Jl. Sudirman Sungailiat',
            'no_wa' => '081234567892',
            'status' => 'aktif',
        ]);

        $this->customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'Budi Pelanggan Servis',
            'no_hp' => '081299887711',
        ]);
    }

    public static function allRolesProvider(): array
    {
        return [
            'Role Owner' => ['Owner'],
            'Role Superadmin' => ['Superadmin'],
            'Role Teknisi' => ['Teknisi'],
            'Role Admin' => ['Admin'],
            'Role Bendahara' => ['Bendahara'],
            'Role Operasional' => ['Operasional'],
            'Role Magang' => ['Magang'],
        ];
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (INDEX)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('servis.masuk'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('allRolesProvider')]
    public function test_all_authenticated_roles_can_access_servis_masuk_page(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('servis.masuk'));
        $response->assertOk();
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('servis.masuk.view', [], $this->cabang1);

        $response = $this->get(route('servis.masuk'));
        $response->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: GRANULAR PERMISSION ACTIONS (CREATE, STATUS, DELETE, TINDAKAN)
    // =========================================================================

    public function test_authorized_roles_can_create_new_servis_masuk(): void
    {
        // Teknisi berhak menambah servis masuk
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        Livewire::test(ServisMasukIndex::class)
            ->call('openCreateModal')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('kategoriPerangkat', 'laptop')
            ->set('tipeUnit', 'Asus ROG Strix G15')
            ->set('keluhan', 'Mati total setelah kena air')
            ->call('simpanServisMasuk')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('servis_masuks', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'tipe_unit' => 'Asus ROG Strix G15',
            'status' => 'pengecekan',
        ]);
    }

    public function test_unauthorized_role_bendahara_blocked_from_creating_servis(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);

        Livewire::test(ServisMasukIndex::class)
            ->call('openCreateModal')
            ->assertForbidden();
    }

    public function test_status_update_allowed_for_teknisi_but_forbidden_for_admin(): void
    {
        $user = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00001',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $user->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Acer Aspire 5',
            'keluhan' => 'No Display',
            'status' => 'antrian',
            'is_real' => true,
        ]);

        // 1. Teknisi berhak mengubah status servis
        Livewire::test(ServisMasukIndex::class)
            ->call('openStatusModal', $servis->id)
            ->set('updateStatus', 'pengecekan')
            ->call('simpanUpdateStatus')
            ->assertHasNoErrors();

        $this->assertEquals('pengecekan', $servis->fresh()->status);

        // 2. Admin DILARANG mengubah status servis (403)
        $this->actingAsRole('Admin', [], $this->cabang1);
        Livewire::test(ServisMasukIndex::class)
            ->call('openStatusModal', $servis->id)
            ->assertForbidden();
    }

    public function test_delete_action_allowed_only_for_superadmin_and_owner(): void
    {
        $user = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00002',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $user->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L3110',
            'keluhan' => 'Blinking paper jam',
            'status' => 'antrian',
            'is_real' => true,
        ]);

        // 1. Teknisi DILARANG menghapus tiket servis (403)
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        Livewire::test(ServisMasukIndex::class)
            ->call('openDeleteModal', $servis->id, $servis->no_servis)
            ->assertForbidden();

        // 2. Superadmin DIIZINKAN menghapus tiket servis
        $this->actingAs($user);
        Livewire::test(ServisMasukIndex::class)
            ->call('openDeleteModal', $servis->id, $servis->no_servis)
            ->assertSet('deleteServisId', $servis->id)
            ->call('konfirmasiHapusServis')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('servis_masuks', [
            'id' => $servis->id,
        ]);
    }

    public function test_tanda_terima_print_route_cross_branch_security(): void
    {
        $teknisiCabang1 = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servisCabang1 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00003',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisiCabang1->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Gaming i5',
            'keluhan' => 'BSOD Blue Screen',
            'status' => 'antrian',
            'is_real' => true,
        ]);

        $servisCabang2 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBSGL-261025-00004',
            'cabang_id' => $this->cabang2->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisiCabang1->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Macbook Pro M1',
            'keluhan' => 'Kernel Panic',
            'status' => 'antrian',
            'is_real' => true,
        ]);

        // 1. Teknisi Cabang 1 BISA cetak tanda terima cabangnya sendiri
        $this->actingAs($teknisiCabang1);
        $this->get(route('servis.tanda-terima', $servisCabang1->id))
            ->assertOk();

        // 2. Teknisi Cabang 1 DILARANG (403) cetak tanda terima cabang 2
        $this->get(route('servis.tanda-terima', $servisCabang2->id))
            ->assertForbidden();

        // 3. Owner BISA cetak tanda terima dari cabang manapun (Bypass)
        $this->actingAsRole('Owner');
        $this->get(route('servis.tanda-terima', $servisCabang2->id))
            ->assertOk();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_servis_masuk_for_all_authorized_roles(): void
    {
        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('servis.masuk'));
    }
}
