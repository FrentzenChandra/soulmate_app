<?php

namespace Tests\Feature\Menu;

use App\Livewire\Servis\Batal\Index as ServisBatalIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class ServisBatalTest extends TestCase
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
            'nama' => 'Joko Susanto',
            'no_hp' => '081298765432',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('servis.batal'))
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
    public function test_role_access_to_servis_batal_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('servis.batal'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('servis.batal.view', [], $this->cabang1);

        $this->get(route('servis.batal'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('servis.batal'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL PULIHKAN STATUS & SERAH TERIMA UNIT BATAL
    // =========================================================================

    public function test_authorized_role_can_serahkan_unit_batal_ke_pelanggan(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00020',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'HP Pavilion 14',
            'keluhan' => 'Biaya sparepart terlalu mahal, batal',
            'status' => 'batal',
            'is_real' => true,
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->call('openAmbilBatalModal', $servis->id)
            ->assertSet('modalAmbilBatalOpen', true)
            ->set('catatanSerahTerima', 'Unit diserahkan kembali lengkap dengan charger')
            ->call('serahkanUnitBatal')
            ->assertHasNoErrors();

        $servisFresh = $servis->fresh();
        $this->assertNotNull($servisFresh->tanggal_ambil);
    }

    public function test_authorized_role_can_pulihkan_status_servis_batal(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00021',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L120',
            'keluhan' => 'Pelanggan berubah pikiran ingin lanjut servis',
            'status' => 'batal',
            'is_real' => true,
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->call('openUbahStatusModal', $servis->id)
            ->assertSet('modalUbahStatusOpen', true)
            ->set('targetStatus', 'perbaikan')
            ->set('catatanUbahStatus', 'Pelanggan setuju dengan estimasi biaya baru')
            ->call('simpanUbahStatus')
            ->assertHasNoErrors();

        $this->assertEquals('perbaikan', $servis->fresh()->status);
    }

    public function test_unauthorized_user_blocked_from_pulihkan_status(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00022',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Kantor Core i5',
            'keluhan' => 'Batal servis',
            'status' => 'batal',
            'is_real' => true,
        ]);

        // User hanya memiliki izin view tanpa servis.batal.ubah_status
        $this->actingAsUserWithPermission(['servis.batal.view'], [], $this->cabang1);

        Livewire::test(ServisBatalIndex::class)
            ->call('openUbahStatusModal', $servis->id)
            ->assertForbidden();
    }

    public function test_detail_modal_shows_complete_batal_data(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00023',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Macbook Air M1',
            'keluhan' => 'Biaya logic board mahal, batal',
            'status' => 'batal',
            'is_real' => true,
        ]);

        Livewire::test(ServisBatalIndex::class)
            ->call('openDetailModal', $servis->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('Joko Susanto');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_servis_batal_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('servis.batal'));
    }

    public function test_sidebar_does_not_display_servis_batal_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Magang', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('servis.batal'));
    }
}

