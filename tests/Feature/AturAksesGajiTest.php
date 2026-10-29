<?php

namespace Tests\Feature;

use App\Livewire\Pengaturan\HakAkses\Index as HakAksesIndex;
use App\Livewire\SlipGaji\Form as SlipGajiForm;
use App\Livewire\SlipGaji\Index as SlipGajiIndex;
use App\Models\Cabang;
use App\Models\SlipGaji;
use App\Models\User;
use App\Services\PermissionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AturAksesGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_schema_permission_service_memuat_slip_gaji_di_bawah_absensi_dan_gaji_staf(): void
    {
        $schema = PermissionService::getModuleSchema();

        $this->assertArrayHasKey('absensi_staf', $schema);
        $this->assertSame('Absensi & Gaji Staf', $schema['absensi_staf']['group_label']);

        $modules = $schema['absensi_staf']['modules'];
        $this->assertArrayHasKey('absensi_riwayat', $modules);
        $this->assertArrayHasKey('slip_gaji', $modules);

        $slipGajiModule = $modules['slip_gaji'];
        $this->assertSame('gaji.view', $slipGajiModule['main_permission']);

        $expectedActions = [
            'gaji.create',
            'gaji.edit',
            'gaji.export_pdf',
            'gaji.reopen',
            'gaji.finalize',
            'gaji.delete',
            'gaji.alert',
            'gaji.tarif.edit',
            'gaji.pokok.edit',
        ];

        foreach ($expectedActions as $act) {
            $this->assertArrayHasKey($act, $slipGajiModule['actions'], "Aksi {$act} harus ada pada schema slip gaji");
        }
    }

    public function test_owner_dapat_mengakses_atur_akses_dan_melihat_modul_slip_gaji(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();

        $this->actingAs($owner);

        Livewire::test(HakAksesIndex::class)
            ->assertOk()
            ->assertSee('Absensi & Gaji Staf')
            ->assertSee('Riwayat Absensi')
            ->assertSee('Slip Gaji')
            ->assertSee('Tambah / Buat Slip Gaji (POST)')
            ->assertSee('Edit Komponen Slip Gaji (PUT)')
            ->assertSee('Cetak & Unduh Dokumen PDF')
            ->assertSee('Buka Kunci Slip Final (Reopen)')
            ->assertSee('Finalkan & Kunci Slip Gaji')
            ->assertSee('Hapus Draft Slip Gaji (DELETE)')
            ->assertSee('Lihat Alert Kenaikan Gaji 6 Bulan')
            ->assertSee('Atur Tarif Global Toko')
            ->assertSee('Ubah Gaji Pokok & Komponen Tetap');
    }

    public function test_non_owner_tidak_dapat_mengakses_atur_akses(): void
    {
        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $this->actingAs($teknisi);

        $this->get(route('master.hak-akses'))
            ->assertForbidden();
    }

    public function test_owner_dapat_menyimpan_izin_gaji_reopen_untuk_role_tertentu(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $roleName = 'Superadmin';
        $role = Role::where('name', $roleName)->first();

        // Pastikan awalnya role belum memiliki gaji.reopen
        $role->revokePermissionTo('gaji.reopen');

        Livewire::test(HakAksesIndex::class)
            ->call('selectRole', $roleName)
            ->set('selectedPermissions', array_merge($role->permissions->pluck('name')->toArray(), [
                'gaji.view',
                'gaji.reopen',
            ]))
            ->call('save')
            ->assertDispatched('swal:success');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('gaji.view'));
        $this->assertTrue($role->hasPermissionTo('gaji.reopen'));
    }

    public function test_user_tanpa_gaji_view_diblokir_dari_menu_gaji(): void
    {
        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($teknisi);

        $this->get(route('slip-gaji.index'))
            ->assertForbidden();
    }

    public function test_user_dengan_gaji_view_tapi_tanpa_gaji_create_diblokir_dari_buat_slip(): void
    {
        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $teknisi->givePermissionTo('gaji.view');
        $this->actingAs($teknisi);

        Livewire::test(SlipGajiForm::class)
            ->assertForbidden();
    }

    public function test_user_tanpa_gaji_reopen_diblokir_dari_membuka_kunci_slip_final(): void
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $admin->givePermissionTo('gaji.view');
        $this->actingAs($admin);

        $slip = SlipGaji::create([
            'no_slip' => 'SLIP-TEST-FINAL',
            'user_id' => $admin->id,
            'cabang_id' => $admin->cabang_id ?? 1,
            'nama_staf_snapshot' => $admin->name,
            'bagian_snapshot' => 'Admin Toko',
            'created_by' => $admin->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2000000,
            'uang_makan_total' => 0,
            'uang_transport_total' => 0,
            'total_bruto' => 2000000,
            'total_pengurangan' => 0,
            'total_diterima' => 2000000,
        ]);

        Livewire::test(SlipGajiIndex::class)
            ->call('confirmReopen', $slip->id, $slip->no_slip)
            ->assertForbidden();
    }

    public function test_user_tanpa_gaji_tarif_edit_diblokir_dari_mengubah_tarif(): void
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $admin->givePermissionTo('gaji.view');
        $this->actingAs($admin);

        Livewire::test(SlipGajiIndex::class)
            ->call('openTarifModal')
            ->assertForbidden();
    }
}

