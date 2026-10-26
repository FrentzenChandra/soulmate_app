<?php

namespace Tests\Feature\Menu;

use App\Livewire\Absensi\Riwayat as AbsensiRiwayat;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class RiwayatAbsensiTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;

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
            'alamat' => 'Jl. Muhidin No. 2',
            'no_wa' => '081234567892',
            'status' => 'aktif',
        ]);
    }

    public static function authorizedRolesProvider(): array
    {
        return [
            'Role Owner' => ['Owner'],
            'Role Superadmin' => ['Superadmin'],
            'Role Teknisi' => ['Teknisi'],
            'Role Admin' => ['Admin'],
            'Role Bendahara' => ['Bendahara'],
        ];
    }

    public static function unauthorizedRolesProvider(): array
    {
        return [
            'Role Operasional' => ['Operasional'],
            'Role Magang' => ['Magang'],
        ];
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('absensi.riwayat'));
        $response->assertRedirect(route('login'));
    }

    #[DataProvider('authorizedRolesProvider')]
    public function test_authorized_roles_can_access_riwayat_absensi(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('absensi.riwayat'));
        $response->assertOk();
    }

    #[DataProvider('unauthorizedRolesProvider')]
    public function test_unauthorized_roles_receive_403_forbidden(string $role): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $response = $this->get(route('absensi.riwayat'));
        $response->assertForbidden();
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('absensi.riwayat', [], $this->cabang1);

        $response = $this->get(route('absensi.riwayat'));
        $response->assertOk();
    }

    public function test_user_with_multi_role_inherits_access(): void
    {
        // Operasional dilarang, tapi jika digabung dengan Admin maka diizinkan
        $this->actingAsUserWithRoles(['Operasional', 'Admin'], [], $this->cabang1);

        $response = $this->get(route('absensi.riwayat'));
        $response->assertOk();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL, SCOPING PRIVASI & FILTER PERIODE
    // =========================================================================

    public function test_staff_sees_only_own_attendance_records(): void
    {
        $teknisi1 = $this->actingAsRole('Teknisi', ['name' => 'Budi Teknisi'], $this->cabang1);
        $teknisi2 = $this->actingAsRole('Teknisi', ['name' => 'Andi Teknisi'], $this->cabang1);

        $today = Carbon::now('Asia/Jakarta');

        // Catatan Budi
        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $teknisi1->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        // Catatan Andi
        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $teknisi2->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        // Login sebagai Budi, hanya boleh melihat Budi, TIDAK boleh melihat Andi
        $this->actingAs($teknisi1);

        Livewire::test(AbsensiRiwayat::class)
            ->assertSee('Budi Teknisi')
            ->assertDontSee('Andi Teknisi');
    }

    public function test_superadmin_sees_all_staff_in_own_branch_only(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);
        $stafCabang1 = $this->actingAsRole('Teknisi', ['name' => 'Staf Cabang PKP'], $this->cabang1);
        $stafCabang2 = $this->actingAsRole('Teknisi', ['name' => 'Staf Cabang SGL'], $this->cabang2);

        $today = Carbon::now('Asia/Jakarta');

        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $stafCabang1->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        Absensi::create([
            'cabang_id' => $this->cabang2->id,
            'user_id' => $stafCabang2->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        // Superadmin Cabang PKP
        $this->actingAs($superadmin);

        Livewire::test(AbsensiRiwayat::class)
            ->assertSee('Staf Cabang PKP')
            ->assertDontSee('Staf Cabang SGL');
    }

    public function test_owner_can_filter_by_branch_and_period(): void
    {
        $owner = $this->actingAsRole('Owner');

        $staf1 = $this->actingAsRole('Admin', ['name' => 'Dedi Admin'], $this->cabang1);
        $staf2 = $this->actingAsRole('Admin', ['name' => 'Sinta Admin'], $this->cabang2);

        $today = Carbon::now('Asia/Jakarta');
        $lastMonth = Carbon::now('Asia/Jakarta')->subMonths(2);

        // Data hari ini di Cabang 1
        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $staf1->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        // Data 2 bulan lalu di Cabang 2
        Absensi::create([
            'cabang_id' => $this->cabang2->id,
            'user_id' => $staf2->id,
            'tanggal' => $lastMonth->toDateString(),
            'waktu_absen' => $lastMonth,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($owner);

        // 1. Mode hari ini -> hanya Dedi
        Livewire::test(AbsensiRiwayat::class)
            ->call('setPeriodeMode', 'hari_ini')
            ->assertSee('Dedi Admin')
            ->assertDontSee('Sinta Admin');

        // 2. Mode semua + filter Cabang 2 -> hanya Sinta
        Livewire::test(AbsensiRiwayat::class)
            ->call('setPeriodeMode', 'semua')
            ->set('cabangFilter', (string) $this->cabang2->id)
            ->assertSee('Sinta Admin')
            ->assertDontSee('Dedi Admin');
    }

    public function test_multi_word_search_filters_staff_name(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $stafTarget = $this->actingAsRole('Admin', ['name' => 'Muhammad Rizki Pratama'], $this->cabang1);
        $stafLain = $this->actingAsRole('Admin', ['name' => 'Siti Aminah'], $this->cabang1);

        $today = Carbon::now('Asia/Jakarta');

        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $stafTarget->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $stafLain->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($superadmin);

        Livewire::test(AbsensiRiwayat::class)
            ->set('search', 'rizki pratama')
            ->assertSee('Muhammad Rizki Pratama')
            ->assertDontSee('Siti Aminah');
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_riwayat_absensi_for_authorized_roles(): void
    {
        // 1. Authorized: Admin
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('absensi.riwayat'));

        // 2. Unauthorized: Operasional
        $this->actingAsRole('Operasional', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('absensi.riwayat'));
    }

    // =========================================================================
    // TAHAP 4: REKAP BULANAN & HAK AKSES PER ROLE
    // =========================================================================

    public function test_rekap_tab_displays_role_ordering_and_excludes_superadmin_owner(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', ['name' => 'Budi Superadmin'], $this->cabang1);

        $stafOp = $this->actingAsRole('Operasional', ['name' => 'Staf Operasional Satu'], $this->cabang1);
        $stafBen = $this->actingAsRole('Bendahara', ['name' => 'Staf Bendahara Satu'], $this->cabang1);
        $stafTek = $this->actingAsRole('Teknisi', ['name' => 'Staf Teknisi Satu'], $this->cabang1);
        $stafAdm = $this->actingAsRole('Admin', ['name' => 'Staf Admin Satu'], $this->cabang1);
        $stafMag = $this->actingAsRole('Magang', ['name' => 'Staf Magang Satu'], $this->cabang1);

        $today = Carbon::now('Asia/Jakarta');

        // Catat absensi terlambat untuk Teknisi
        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $stafTek->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today->copy()->setTime(8, 15, 0),
            'shift' => 'pagi',
            'status_kehadiran' => 'terlambat',
            'menit_keterlambatan' => 15,
            'snapshot_jam_masuk' => '08:00:00',
            'snapshot_toleransi_menit' => 2,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($superadmin);

        Livewire::test(AbsensiRiwayat::class)
            ->call('switchTab', 'rekap_bulanan')
            ->assertSee('Divisi Operasional')
            ->assertSee('Divisi Bendahara')
            ->assertSee('Divisi Teknisi')
            ->assertSee('Divisi Admin')
            ->assertSee('Divisi Magang')
            ->assertSee('Staf Operasional Satu')
            ->assertSee('Staf Bendahara Satu')
            ->assertSee('Staf Teknisi Satu')
            ->assertSee('Staf Admin Satu')
            ->assertSee('Staf Magang Satu')
            ->assertSee('15 Menit')
            ->assertDontSee('Budi Superadmin'); // Superadmin tidak ikut rekap absensi
    }

    public function test_staff_without_rekap_permission_only_sees_self_in_rekap(): void
    {
        $staf1 = $this->actingAsRole('Admin', ['name' => 'Staff Saya Sendiri'], $this->cabang1);
        $staf2 = $this->actingAsRole('Admin', ['name' => 'Staff Orang Lain'], $this->cabang1);

        $this->actingAs($staf1);

        Livewire::test(AbsensiRiwayat::class)
            ->call('switchTab', 'rekap_bulanan')
            ->assertSee('Staff Saya Sendiri')
            ->assertDontSee('Staff Orang Lain');
    }

    public function test_staff_with_direct_permission_can_view_branch_team_rekap(): void
    {
        $staf1 = $this->actingAsRole('Admin', ['name' => 'Staff Pemimpin Tim'], $this->cabang1);
        $staf2 = $this->actingAsRole('Teknisi', ['name' => 'Anggota Tim'], $this->cabang1);

        $staf1->givePermissionTo('absensi.rekap.view');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($staf1);

        Livewire::test(AbsensiRiwayat::class)
            ->call('switchTab', 'rekap_bulanan')
            ->assertSee('Staff Pemimpin Tim')
            ->assertSee('Anggota Tim');
    }

    public function test_late_detail_modal_opens_and_shows_late_occurrences(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);
        $staf = $this->actingAsRole('Teknisi', ['name' => 'Randi Teknisi'], $this->cabang1);

        $today = Carbon::now('Asia/Jakarta');

        Absensi::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $staf->id,
            'tanggal' => $today->toDateString(),
            'waktu_absen' => $today->copy()->setTime(8, 25, 0),
            'shift' => 'pagi',
            'status_kehadiran' => 'terlambat',
            'menit_keterlambatan' => 25,
            'snapshot_jam_masuk' => '08:00:00',
            'snapshot_toleransi_menit' => 2,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($superadmin);

        Livewire::test(AbsensiRiwayat::class)
            ->call('switchTab', 'rekap_bulanan')
            ->call('openDetailLate', $staf->id)
            ->assertSet('modalDetailLateOpen', true)
            ->assertSee('Rincian Keterlambatan: Randi Teknisi')
            ->assertSee('+25 Menit')
            ->call('closeDetailLate')
            ->assertSet('modalDetailLateOpen', false);
    }
}

