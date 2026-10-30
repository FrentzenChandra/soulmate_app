<?php

namespace Tests\Feature\Menu;

use App\Livewire\SlipGaji\Form as SlipGajiForm;
use App\Livewire\SlipGaji\Index as SlipGajiIndex;
use App\Models\Cabang;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use App\Models\TarifGaji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class SlipGajiMenuTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected User $stafTeknisi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupRolesAndPermissions();

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Cabang Pangkalpinang',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Merdeka No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->stafTeknisi = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi.teknisi@soulmate.test',
            'cabang_id' => $this->cabang1->id,
            'status' => 'aktif',
            'bagian' => 'Teknisi Laptop',
            'tanggal_mulai_bekerja' => '2022-01-15',
            'tanggal_lahir' => '1998-05-20',
        ]);
        $this->stafTeknisi->assignRole('Teknisi');

        TarifGaji::updateOrCreate(['jenis' => 'uang_makan'], ['nominal' => 20000]);
        TarifGaji::updateOrCreate(['jenis' => 'uang_transport'], ['nominal' => 20000]);
        TarifGaji::updateOrCreate(['jenis' => 'kenaikan_6_bulan'], ['nominal' => 100000]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (EKSKLUSIF OWNER, SUPERADMIN, ADMIN, BENDAHARA, OPERASIONAL)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('slip-gaji.index'))
            ->assertRedirect(route('login'));

        $this->get(route('slip-gaji.create'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Owner has access via bypass' => ['Owner', 200],
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Operasional has access' => ['Operasional', 200],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_slip_gaji_index_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('slip-gaji.index'))
            ->assertStatus($expectedStatus);
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_slip_gaji_create_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('slip-gaji.create'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission(['gaji.view'], [], $this->cabang1);

        $this->get(route('slip-gaji.index'))
            ->assertOk();
    }

    public function test_user_with_multi_role_inherits_access(): void
    {
        $this->actingAsUserWithRoles(['Teknisi', 'Admin'], [], $this->cabang1);

        $this->get(route('slip-gaji.index'))
            ->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD & LOGIKA BISNIS SLIP GAJI
    // =========================================================================

    public function test_create_draft_slip_gaji_happy_path(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $this->stafTeknisi->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 2500000)
            ->set('uangMakanHari', 25)
            ->set('uangMakanPerHari', 20000)
            ->set('uangTransportHari', 25)
            ->set('uangTransportPerHari', 20000)
            ->call('tambahPenerimaan')
            ->set('penerimaanItems.0.nama_item', 'Servisan Selesai Laptop')
            ->set('penerimaanItems.0.nominal', 300000)
            ->call('tambahPotongan')
            ->set('penguranganItems.0.nama_item', 'Kasbon Makan Siang')
            ->set('penguranganItems.0.nominal', 50000)
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        $this->assertDatabaseHas('slip_gajis', [
            'user_id' => $this->stafTeknisi->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 2500000,
            'total_bruto' => 3800000,
            'total_pengurangan' => 50000,
            'total_diterima' => 3750000,
        ]);
    }

    public function test_finalize_slip_gaji_freezes_snapshot(): void
    {
        $this->actingAsRole('Owner', [], $this->cabang1);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $this->stafTeknisi->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 2500000)
            ->set('uangMakanHari', 20)
            ->set('uangMakanPerHari', 20000)
            ->set('uangTransportHari', 20)
            ->set('uangTransportPerHari', 20000)
            ->call('finalize')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        $slip = SlipGaji::where('user_id', $this->stafTeknisi->id)->first();
        $this->assertNotNull($slip);
        $this->assertEquals('final', $slip->status);
        $this->assertEquals($this->stafTeknisi->name, $slip->nama_staf_snapshot);
        $this->assertEquals('Teknisi Laptop', $slip->bagian_snapshot);
        $this->assertNotNull($slip->finalized_at);
    }

    public function test_finalize_fails_when_gaji_pokok_is_blank_or_zero(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $this->stafTeknisi->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', null)
            ->call('finalize')
            ->assertDispatched('swal:toast');

        $this->assertDatabaseMissing('slip_gajis', [
            'user_id' => $this->stafTeknisi->id,
            'status' => 'final',
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND & LIVEWIRE UI BEHAVIOR PER ROLE
    // =========================================================================

    public function test_index_component_renders_search_and_filters(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/001',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $this->stafTeknisi->id,
            'nama_staf_snapshot' => $this->stafTeknisi->name,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 2500000,
            'total_bruto' => 3300000,
            'total_pengurangan' => 0,
            'total_diterima' => 3300000,
            'created_by' => auth()->id(),
        ]);

        Livewire::test(SlipGajiIndex::class)
            ->assertSee($this->stafTeknisi->name)
            ->assertSee('SLIP/PKP/2026/10/001')
            ->assertSee('Draft')
            ->set('search', 'NonExistentStaffName')
            ->assertDontSee('SLIP/PKP/2026/10/001');
    }

    public function test_owner_can_reopen_finalized_slip(): void
    {
        $this->actingAsRole('Owner', [], $this->cabang1);

        $slip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/002',
            'cabang_id' => $this->cabang1->id,
            'user_id' => $this->stafTeknisi->id,
            'nama_staf_snapshot' => $this->stafTeknisi->name,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2500000,
            'total_bruto' => 3300000,
            'total_pengurangan' => 0,
            'total_diterima' => 3300000,
            'created_by' => auth()->id(),
            'finalized_by' => auth()->id(),
            'finalized_at' => now(),
        ]);

        Livewire::test(SlipGajiIndex::class)
            ->call('confirmReopen', $slip->id, $slip->no_slip)
            ->set('alasanBukaKembali', 'Koreksi perhitungan lembur')
            ->call('executeReopen')
            ->assertHasNoErrors();

        $slip->refresh();
        $this->assertEquals('draft', $slip->status);
        $this->assertEquals('Koreksi perhitungan lembur', $slip->alasan_buka_kembali);
    }
}

