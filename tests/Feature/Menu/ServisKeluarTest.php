<?php

namespace Tests\Feature\Menu;

use App\Livewire\Servis\Keluar\Index as ServisKeluarIndex;
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

class ServisKeluarTest extends TestCase
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
            'nama' => 'Budi Santoso',
            'no_hp' => '081987654321',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('servis.keluar'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Admin has access' => ['Admin', 200],
            'Bendahara has access' => ['Bendahara', 200],
            'Operasional has access' => ['Operasional', 200],
            'Magang has access' => ['Magang', 200],
            'Owner has access via bypass' => ['Owner', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_all_authenticated_roles_can_access_servis_keluar_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('servis.keluar'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('servis.keluar.view', [], $this->cabang1);

        $this->get(route('servis.keluar'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('servis.keluar'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL PENGAMBILAN & PELUNASAN KASIR
    // =========================================================================

    public function test_authorized_role_can_process_penyerahan_dan_pelunasan_unit(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00010',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad X280',
            'keluhan' => 'Keyboard sebagian macet',
            'status' => 'selesai',
            'is_real' => true,
            'total_biaya' => 250000,
            'estimasi_biaya' => 250000,
        ]);

        ServisDetail::create([
            'servis_id' => $servis->id,
            'nama_item' => 'Jasa Ganti Keyboard ThinkPad',
            'jenis' => 'jasa',
            'qty' => 1,
            'harga' => 250000,
            'subtotal' => 250000,
            'garansi_hari' => 30,
        ]);

        // Login sebagai Admin yang berhak melakukan serah terima & pelunasan
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openAmbilModal', $servis->id)
            ->assertSet('modalAmbilOpen', true)
            ->assertSet('nominalBayar', 250000)
            ->set('metodePembayaran', 'cash')
            ->set('nominalBayar', 300000)
            ->call('prosesPenyerahanUnit')
            ->assertHasNoErrors();

        $servisFresh = $servis->fresh();
        $this->assertEquals('sudah_diambil', $servisFresh->status);
        $this->assertEquals(300000, $servisFresh->bayar);
        $this->assertEquals(50000, $servisFresh->kembalian);
        $this->assertEquals($admin->id, $servisFresh->kasir_id);
    }

    public function test_unauthorized_role_magang_blocked_from_penyerahan_unit(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00011',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Canon IP2770',
            'keluhan' => 'Blinking E05',
            'status' => 'selesai',
            'is_real' => true,
            'total_biaya' => 150000,
        ]);

        // Magang tidak memiliki izin servis.keluar.ambil
        $this->actingAsRole('Magang', [], $this->cabang1);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openAmbilModal', $servis->id)
            ->assertForbidden();
    }

    public function test_detail_modal_shows_complete_servis_data(): void
    {
        $teknisi = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00012',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisi->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Kasir Core i3',
            'keluhan' => 'Power supply drop',
            'status' => 'selesai',
            'is_real' => true,
            'total_biaya' => 350000,
        ]);

        Livewire::test(ServisKeluarIndex::class)
            ->call('openDetailModal', $servis->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('Budi Santoso');
    }

    // =========================================================================
    // TAHAP 2C: CROSS BRANCH SECURITY & INVOICE PRINT
    // =========================================================================

    public function test_invoice_print_cross_branch_security(): void
    {
        $teknisiCabang1 = $this->actingAsRole('Teknisi', [], $this->cabang1);

        $servisCabang1 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBPKP-261025-00013',
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisiCabang1->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus Vivobook 14',
            'keluhan' => 'Engsel patah',
            'status' => 'selesai',
            'is_real' => true,
            'total_biaya' => 150000,
        ]);

        $servisCabang2 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CBSGL-261025-00014',
            'cabang_id' => $this->cabang2->id,
            'customer_id' => $this->customer->id,
            'created_by' => $teknisiCabang1->id,
            'tanggal_masuk' => now()->toDateString(),
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Dell Latitude 7490',
            'keluhan' => 'Ganti baterai',
            'status' => 'selesai',
            'is_real' => true,
            'total_biaya' => 450000,
        ]);

        // 1. Teknisi Cabang 1 BISA cetak invoice cabangnya sendiri
        $this->actingAs($teknisiCabang1);
        $this->get(route('servis.invoice', $servisCabang1->id))
            ->assertOk();

        // 2. Teknisi Cabang 1 DILARANG (403) cetak invoice cabang 2
        $this->get(route('servis.invoice', $servisCabang2->id))
            ->assertForbidden();

        // 3. User Operasional tidak memiliki permission servis.keluar.print -> DITOLAK (403)
        $this->actingAsRole('Operasional', [], $this->cabang1);
        $this->get(route('servis.invoice', $servisCabang1->id))
            ->assertForbidden();

        // 4. Owner BISA cetak invoice dari cabang manapun (Bypass)
        $this->actingAsRole('Owner');
        $this->get(route('servis.invoice', $servisCabang2->id))
            ->assertOk();
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_servis_keluar_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('servis.keluar'));
    }
}

