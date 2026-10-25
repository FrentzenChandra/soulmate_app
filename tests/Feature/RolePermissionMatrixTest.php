<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\User;
use Database\Seeders\PermissionMatrixSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed seluruh permissions dan default role presets dari PermissionService
        $this->seed(PermissionMatrixSeeder::class);

        $this->cabang = Cabang::create([
            'nama_cabang' => 'Soulmate Pusat PKP',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Masjid Jamik No. 1',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);
    }

    protected function createStaff(string $roleName): User
    {
        $user = User::create([
            'name' => "Staf {$roleName}",
            'username' => strtolower($roleName) . '_' . uniqid(),
            'email' => strtolower($roleName) . '_' . uniqid() . '@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);

        $user->assignRole($roleName);

        return $user;
    }

    protected function createOwner(): User
    {
        $owner = User::create([
            'name' => 'Owner Eksekutif',
            'username' => 'owner_' . uniqid(),
            'email' => 'owner_' . uniqid() . '@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);

        $owner->assignRole('Owner');

        return $owner;
    }

    /**
     * ROLE 1: OWNER (Global Bypass via Gate::before)
     * Dapat mengakses 100% rute, termasuk rute khusus HQ (Hak Akses, Cabang, PPN).
     */
    public function test_owner_has_full_bypass_and_can_access_all_routes(): void
    {
        $owner = $this->createOwner();
        $this->actingAs($owner);

        // Rute HQ Owner
        $this->get(route('master.hak-akses'))->assertOk();
        $this->get(route('master.cabang'))->assertOk();
        $this->get(route('master.ppn'))->assertOk();

        // Rute Operasional & Keuangan
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('laporan.penjualan'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.keluar'))->assertOk();
        $this->get(route('servis.batal'))->assertOk();
        $this->get(route('servis.non-real'))->assertOk();
        $this->get(route('transaksi.full'))->assertOk();
        $this->get(route('transaksi.harian'))->assertOk();
        $this->get(route('faktur-pajak.index'))->assertOk();
        $this->get(route('kas-keluar'))->assertOk();
        $this->get(route('saldo.index'))->assertOk();
        $this->get(route('target.bulanan'))->assertOk();
        $this->get(route('master.produk'))->assertOk();
        $this->get(route('master.orang-pajak'))->assertOk();
        $this->get(route('master.customer'))->assertOk();
        $this->get(route('master.users'))->assertOk();
        $this->get(route('master.servis'))->assertOk();
        $this->get(route('master.pegawai'))->assertOk();
        $this->get(route('klaim-garansi.index'))->assertOk();
        $this->get(route('absensi.index'))->assertOk();
        $this->get(route('absensi.riwayat'))->assertOk();

        // Frontend Sidebar: Owner melihat navigasi Atur Akses dan Master Cabang
        $response = $this->get(route('dashboard'));
        $response->assertSee('Atur Akses');
        $response->assertSee('Master Cabang');
    }

    /**
     * ROLE 2: SUPERADMIN
     * Akses penuh seluruh modul cabang, tetapi DITOLAK dari rute khusus Owner HQ.
     */
    public function test_superadmin_has_full_branch_access_but_forbidden_from_owner_hq_routes(): void
    {
        $superadmin = $this->createStaff('Superadmin');
        $this->actingAs($superadmin);

        // Diizinkan pada seluruh operasional cabang
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.non-real'))->assertOk();
        $this->get(route('transaksi.full'))->assertOk();
        $this->get(route('transaksi.harian'))->assertOk();
        $this->get(route('kas-keluar'))->assertOk();
        $this->get(route('saldo.index'))->assertOk();
        $this->get(route('master.users'))->assertOk();
        $this->get(route('klaim-garansi.index'))->assertOk();
        $this->get(route('absensi.riwayat'))->assertOk();

        // DILARANG keras mengakses rute HQ Owner
        $this->get(route('master.hak-akses'))->assertForbidden();
        $this->get(route('master.cabang'))->assertForbidden();
        $this->get(route('master.ppn'))->assertForbidden();

        // Frontend Sidebar Superadmin: Menu Owner HQ tidak boleh muncul
        $response = $this->get(route('dashboard'));
        $response->assertDontSee('Atur Akses');
        $response->assertDontSee('Master Cabang');
    }

    /**
     * ROLE 3: TEKNISI
     * Akses fokus pada Servis, Klaim Garansi, Presensi & Riwayat Absensi.
     * Dilarang dari Transaksi, Kas Keuangan, dan Master Pengguna.
     */
    public function test_teknisi_role_permissions_and_sidebar_visibility(): void
    {
        $teknisi = $this->createStaff('Teknisi');
        $this->actingAs($teknisi);

        // Rute DISETUJUI
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.keluar'))->assertOk();
        $this->get(route('servis.batal'))->assertOk();
        $this->get(route('servis.non-real'))->assertOk();
        $this->get(route('klaim-garansi.index'))->assertOk();
        $this->get(route('absensi.index'))->assertOk();
        $this->get(route('absensi.riwayat'))->assertOk();
        $this->get(route('cabang.stok'))->assertOk();

        // Rute DITOLAK (403)
        $this->get(route('transaksi.full'))->assertForbidden();
        $this->get(route('transaksi.harian'))->assertForbidden();
        $this->get(route('kas-keluar'))->assertForbidden();
        $this->get(route('saldo.index'))->assertForbidden();
        $this->get(route('faktur-pajak.index'))->assertForbidden();
        $this->get(route('target.bulanan'))->assertForbidden();
        $this->get(route('master.users'))->assertForbidden();

        // Frontend Sidebar: Memastikan menu servis muncul, menu keuangan & setting disembunyikan
        $response = $this->get(route('dashboard'));
        $response->assertSee('Servis Masuk');
        $response->assertSee('Klaim Garansi');
        $response->assertSee('Riwayat Absensi');
        $response->assertDontSee('Transaksi Full');
        $response->assertDontSee('Transaksi Harian');
        $response->assertDontSee('Kas Keluar');
    }

    /**
     * ROLE 4: ADMIN
     * Akses kasir, transaksi harian, servis, klaim garansi, data pajak (Master WP).
     * Dilarang dari Transaksi Full (sensitivitas margin laba) & Master Pengguna.
     */
    public function test_admin_role_permissions_and_sidebar_visibility(): void
    {
        $admin = $this->createStaff('Admin');
        $this->actingAs($admin);

        // Rute DISETUJUI
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('laporan.penjualan'))->assertOk();
        $this->get(route('transaksi.harian'))->assertOk();
        $this->get(route('faktur-pajak.index'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.non-real'))->assertOk();
        $this->get(route('klaim-garansi.index'))->assertOk();
        $this->get(route('master.orang-pajak'))->assertOk();
        $this->get(route('absensi.index'))->assertOk();
        $this->get(route('absensi.riwayat'))->assertOk();

        // Rute DITOLAK (403)
        $this->get(route('transaksi.full'))->assertForbidden();
        $this->get(route('saldo.index'))->assertForbidden();
        $this->get(route('master.users'))->assertForbidden();

        // Frontend Sidebar: Transaksi Harian & Master WP muncul; Transaksi Full tersembunyi
        $response = $this->get(route('dashboard'));
        $response->assertSee('Transaksi Harian');
        $response->assertSee('Master WP');
        $response->assertDontSee('Transaksi Full');
        $response->assertDontSee('Master Pengguna');
    }

    /**
     * ROLE 5: BENDAHARA
     * Akses kas keluar, saldo, transaksi harian, pajak, kasir.
     * Dilarang dari Transaksi Full & Master Pengguna.
     */
    public function test_bendahara_role_permissions_and_sidebar_visibility(): void
    {
        $bendahara = $this->createStaff('Bendahara');
        $this->actingAs($bendahara);

        // Rute DISETUJUI
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kas-keluar'))->assertOk();
        $this->get(route('saldo.index'))->assertOk();
        $this->get(route('transaksi.harian'))->assertOk();
        $this->get(route('faktur-pajak.index'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('master.orang-pajak'))->assertOk();

        // Rute DITOLAK (403)
        $this->get(route('transaksi.full'))->assertForbidden();
        $this->get(route('master.users'))->assertForbidden();

        // Frontend Sidebar: Kas Keluar & Saldo muncul; Transaksi Full tersembunyi
        $response = $this->get(route('dashboard'));
        $response->assertSee('Kas Keluar');
        $response->assertSee('Saldo');
        $response->assertSee('Master WP');
        $response->assertDontSee('Transaksi Full');
    }

    /**
     * ROLE 6: OPERASIONAL
     * Akses operasional harian cepat: Kasir POS, Absensi Harian, Servis Masuk/Keluar, Klaim Garansi.
     * Dilarang dari modul Keuangan, Riwayat Absensi, dan Transaksi.
     */
    public function test_operasional_role_permissions_and_sidebar_visibility(): void
    {
        $operasional = $this->createStaff('Operasional');
        $this->actingAs($operasional);

        // Rute DISETUJUI
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('absensi.index'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.keluar'))->assertOk();
        $this->get(route('klaim-garansi.index'))->assertOk();

        // Rute DITOLAK (403)
        $this->get(route('transaksi.full'))->assertForbidden();
        $this->get(route('transaksi.harian'))->assertForbidden();
        $this->get(route('kas-keluar'))->assertForbidden();
        $this->get(route('saldo.index'))->assertForbidden();
        $this->get(route('absensi.riwayat'))->assertForbidden();
        $this->get(route('master.users'))->assertForbidden();

        // Frontend Sidebar: Kasir & Servis muncul; Riwayat Absensi & Kas tersembunyi
        $response = $this->get(route('dashboard'));
        $response->assertSee('Kasir POS');
        $response->assertSee('Absensi');
        $response->assertDontSee('Riwayat Absensi');
        $response->assertDontSee('Kas Keluar');
    }

    /**
     * ROLE 7: MAGANG
     * Hak akses paling minimal: hanya pantau servis masuk/keluar, kasir baca, master produk/servis.
     * Dilarang dari Keuangan, Pajak, Garansi, Riwayat Absensi, dan Hapus Data.
     */
    public function test_magang_role_permissions_and_sidebar_visibility(): void
    {
        $magang = $this->createStaff('Magang');
        $this->actingAs($magang);

        // Rute DISETUJUI
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('servis.masuk'))->assertOk();
        $this->get(route('servis.keluar'))->assertOk();
        $this->get(route('kasir.produk'))->assertOk();
        $this->get(route('master.produk'))->assertOk();

        // Rute DITOLAK (403)
        $this->get(route('transaksi.full'))->assertForbidden();
        $this->get(route('transaksi.harian'))->assertForbidden();
        $this->get(route('kas-keluar'))->assertForbidden();
        $this->get(route('saldo.index'))->assertForbidden();
        $this->get(route('faktur-pajak.index'))->assertForbidden();
        $this->get(route('absensi.riwayat'))->assertForbidden();
        $this->get(route('klaim-garansi.index'))->assertForbidden();
        $this->get(route('master.users'))->assertForbidden();

        // Frontend Sidebar Magang
        $response = $this->get(route('dashboard'));
        $response->assertDontSee('Riwayat Absensi');
        $response->assertDontSee('Klaim Garansi');
        $response->assertDontSee('Kas Keluar');
    }

    /**
     * Uji Proteksi Mutasi Aksi (DELETE / MUTATION):
     * Memastikan role tanpa izin delete ditolak saat mencoba eksekusi aksi.
     */
    public function test_action_mutation_guards_on_delete_and_modifications(): void
    {
        // 1. Buat data Kas Keluar
        $kasKeluar = KasKeluar::create([
            'cabang_id' => $this->cabang->id,
            'kategori' => 'Operasional',
            'jumlah' => 50000,
            'keterangan' => 'Beli ATK Toko',
            'tanggal' => now()->toDateString(),
            'user_id' => $this->createOwner()->id,
        ]);

        // 2. Teknisi TIDAK memiliki izin 'kas.keluar.delete'
        $teknisi = $this->createStaff('Teknisi');
        $this->assertFalse($teknisi->can('kas.keluar.delete'));

        // 3. Bendahara MEMILIKI izin 'kas.keluar.delete'
        $bendahara = $this->createStaff('Bendahara');
        $this->assertTrue($bendahara->can('kas.keluar.delete'));

        // 4. Magang TIDAK memiliki izin 'klaim_garansi.delete' maupun 'kas.keluar.delete'
        $magang = $this->createStaff('Magang');
        $this->assertFalse($magang->can('kas.keluar.delete'));
        $this->assertFalse($magang->can('klaim_garansi.delete'));
    }

    /**
     * Uji Sinkronisasi Izin Dinamis (Dynamic Live Matrix):
     * Perubahan izin role secara dinamis langsung berdampak seketika pada user.
     */
    public function test_dynamic_role_permission_revocation_and_grant(): void
    {
        $teknisi = $this->createStaff('Teknisi');
        $roleTeknisi = Role::findByName('Teknisi', 'web');

        // Awal: Teknisi diizinkan akses klaim garansi
        $this->assertTrue($teknisi->can('klaim_garansi.view'));

        // Cabut izin 'klaim_garansi.view' dari role Teknisi
        $currentPermissions = $roleTeknisi->permissions->pluck('name')->toArray();
        $updatedPermissions = array_diff($currentPermissions, ['klaim_garansi.view']);
        $roleTeknisi->syncPermissions($updatedPermissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Refresh model user
        $teknisi->refresh();

        // Sekarang: Teknisi otomatis ditolak dari klaim garansi
        $this->assertFalse($teknisi->can('klaim_garansi.view'));

        $this->actingAs($teknisi);
        $this->get(route('klaim-garansi.index'))->assertForbidden();
    }

    /**
     * UJI EXHAUSTIVE: 6 Role Staf × 25 Rute Operasional Sidebar
     * Memastikan 100% rute secara konsisten mengembalikan 200 OK jika memiliki izin,
     * dan mengembalikan 403 Forbidden jika tidak memiliki izin.
     */
    public function test_exhaustive_matrix_all_roles_across_all_sidebar_routes(): void
    {
        $routesMap = [
            'absensi.index' => 'absensi.view',
            'kasir.produk' => 'kasir.produk.view',
            'laporan.penjualan' => 'laporan.penjualan.view',
            'servis.masuk' => 'servis.masuk.view',
            'servis.keluar' => 'servis.keluar.view',
            'servis.batal' => 'servis.batal.view',
            'servis.non-real' => 'servis.non_real.view',
            'transaksi.harian' => 'transaksi.harian.view',
            'transaksi.full' => 'transaksi.full.view',
            'faktur-pajak.index' => 'pajak.view',
            'kas-keluar' => 'kas.keluar.view',
            'saldo.index' => 'saldo.view',
            'serial-number' => 'serial_number.view',
            'target.bulanan' => 'target.bulanan.view',
            'master.produk' => 'master.produk.view',
            'cabang.stok' => 'cabang.stok.view',
            'master.orang-pajak' => 'master.orang_pajak.view',
            'master.customer' => 'master.customer.view',
            'master.users' => 'master.users.view',
            'master.servis' => 'master.servis.view',
            'master.pegawai' => 'master.pegawai.view',
            'master.distributor' => 'master.distributor.view',
            'master.wifi-absen' => 'master.wifi_absensi.view',
            'absensi.riwayat' => 'absensi.riwayat',
            'klaim-garansi.index' => 'klaim_garansi.view',
        ];

        $roles = ['Superadmin', 'Teknisi', 'Admin', 'Bendahara', 'Operasional', 'Magang'];

        foreach ($roles as $roleName) {
            $user = $this->createStaff($roleName);
            $this->actingAs($user);

            foreach ($routesMap as $routeName => $permission) {
                $hasPermission = $user->can($permission);
                $response = $this->get(route($routeName));

                if ($hasPermission) {
                    $this->assertEquals(
                        200,
                        $response->status(),
                        "Role [{$roleName}] SEHARUSNYA BISA akses rute [{$routeName}] dengan permission [{$permission}], tetapi mendapat {$response->status()}."
                    );
                } else {
                    $this->assertEquals(
                        403,
                        $response->status(),
                        "Role [{$roleName}] SEHARUSNYA DITOLAK (403) dari rute [{$routeName}] karena tidak punya permission [{$permission}], tetapi mendapat {$response->status()}."
                    );
                }
            }
        }
    }
}

