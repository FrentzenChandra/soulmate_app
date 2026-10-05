<?php

namespace Tests\Feature;

use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
use App\Models\TransaksiFull;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TargetBulananTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_target_bulanan()
    {
        $this->get(route('target.bulanan'))->assertRedirect(route('login'));
    }

    public function test_owner_and_superadmin_can_access_target_bulanan()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);
        $this->get(route('target.bulanan'))->assertStatus(200);

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);
        $this->get(route('target.bulanan'))->assertStatus(200);
    }

    public function test_unauthorized_roles_cannot_access_target_bulanan()
    {
        $teknisi = User::role('Teknisi')->first();
        $this->actingAs($teknisi);
        $this->get(route('target.bulanan'))->assertStatus(403);

        $admin = User::role('Admin')->first();
        $this->actingAs($admin);
        $this->get(route('target.bulanan'))->assertStatus(403);

        $bendahara = User::role('Bendahara')->first();
        $this->actingAs($bendahara);
        $this->get(route('target.bulanan'))->assertStatus(403);
    }

    public function test_employee_realization_matrix_calculated_purely_from_transaksi_full()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        TransaksiFull::query()->delete();
        TargetDivisi::query()->delete();

        $pegawai = Pegawai::firstOrCreate(
            ['cabang_id' => $superadmin->cabang_id ?: 1, 'nama' => 'Staf Uji'],
            ['role' => 'Teknisi', 'is_aktif' => true]
        );

        // Create TransaksiFull for Laptop in January 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260110-00001',
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'pegawai_id' => $pegawai->id,
            'nama_pelanggan' => 'Pelanggan Test 1',
            'no_hp' => '08123456789',
            'tipe_transaksi' => 'servis',
            'kategori_perangkat' => 'laptop',
            'tanggal' => '2026-01-10 10:00:00',
            'total_modal' => 50000,
            'total_jual' => 150000,
            'total_laba' => 100000,
            'grand_total' => 150000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        // Create TransaksiFull for Printer in February 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260215-00002',
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'pegawai_id' => $pegawai->id,
            'nama_pelanggan' => 'Pelanggan Test 2',
            'no_hp' => '08123456788',
            'tipe_transaksi' => 'servis',
            'kategori_perangkat' => 'printer',
            'tanggal' => '2026-02-15 10:00:00',
            'total_modal' => 70000,
            'total_jual' => 200000,
            'total_laba' => 130000,
            'grand_total' => 200000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        // Create TransaksiFull for Penjualan in March 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260320-00003',
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'pegawai_id' => $pegawai->id,
            'nama_pelanggan' => 'Pelanggan Test 3',
            'no_hp' => '08123456787',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => '2026-03-20 10:00:00',
            'total_modal' => 200000,
            'total_jual' => 500000,
            'total_laba' => 300000,
            'grand_total' => 500000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        $test = Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026);

        $test->assertViewHas('userMatrix', function ($userMatrix) use ($pegawai) {
            $pegawaiRow = collect($userMatrix)->firstWhere('user.id', $pegawai->id);
            return $pegawaiRow !== null
                && $pegawaiRow['months'][1]['count'] === 1
                && $pegawaiRow['months'][2]['count'] === 1
                && $pegawaiRow['months'][3]['count'] === 1
                && $pegawaiRow['total_ytd'] === 3;
        });
    }

    public function test_superadmin_can_set_annual_target_for_all_divisions_5in1_modal()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openSetTargetTahunanDivisi')
            ->assertSet('modalSetTargetTahunanDivisiOpen', true)
            ->assertSet('formTahunTargetDivisi', 2026)
            ->set('formTargetTahunanLaptop', 120)
            ->set('formTargetTahunanPrinter', 60)
            ->set('formTargetTahunanKomputer', 48)
            ->set('formTargetTahunanAdmin', 240)
            ->call('simpanTargetTahunanDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalSetTargetTahunanDivisiOpen', false);

        $cabangId = $superadmin->cabang_id ?: 1;

        $targetLaptop = TargetDivisi::where('cabang_id', $cabangId)->where('divisi', 'laptop')->where('tahun', 2026)->first();
        $this->assertNotNull($targetLaptop);
        $this->assertEquals(120, $targetLaptop->target_bulanan);
        $this->assertEquals(120, $targetLaptop->monthly_targets['1']);

        $targetPrinter = TargetDivisi::where('cabang_id', $cabangId)->where('divisi', 'printer')->where('tahun', 2026)->first();
        $this->assertNotNull($targetPrinter);
        $this->assertEquals(60, $targetPrinter->target_bulanan);
        $this->assertEquals(60, $targetPrinter->monthly_targets['1']);

        // Verify direct annual target reflection in divisiMatrix
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return isset($divisiMatrix['laptop'])
                    && $divisiMatrix['laptop']['target_tahunan_input'] === 120
                    && $divisiMatrix['printer']['target_tahunan_input'] === 60
                    && $divisiMatrix['komputer']['target_tahunan_input'] === 48
                    && $divisiMatrix['admin']['target_tahunan_input'] === 240;
            });
    }

    public function test_superadmin_can_edit_monthly_custom_realisasi_divisi()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        // Setup annual target first
        TargetDivisi::updateOrCreate(
            ['cabang_id' => $superadmin->cabang_id ?: 1, 'divisi' => 'printer', 'tahun' => 2026],
            ['target_bulanan' => 25]
        );

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditCellDivisi', 'printer', 5)
            ->assertSet('editDivisiKey', 'printer')
            ->assertSet('editDivisiBulan', 5)
            ->assertSet('targetSyaratBulananInfo', 25)
            ->set('formRealisasiDivisi', 30)
            ->call('simpanEditCellDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalEditCellDivisiOpen', false);

        $targetDivisi = TargetDivisi::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('divisi', 'printer')
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($targetDivisi);
        $this->assertEquals(30, $targetDivisi->monthly_realisasi['5']);

        // Test rendering reflection
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return isset($divisiMatrix['printer'])
                    && $divisiMatrix['printer']['months'][5]['target'] === 25
                    && $divisiMatrix['printer']['months'][5]['count'] === 30
                    && $divisiMatrix['printer']['months'][5]['percent'] === 120
                    && $divisiMatrix['printer']['months'][5]['status'] === 'tercapai';
            });
    }

    public function test_teknisi_with_target_edit_permission_can_manage_targets()
    {
        $teknisi = User::role('Teknisi')->first();
        $teknisi->givePermissionTo(['target.bulanan.view', 'target.bulanan.edit']);
        $this->actingAs($teknisi);

        // Can view page
        $this->get(route('target.bulanan'))->assertStatus(200);

        // Can open and save annual target
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertSet('canManageTarget', true)
            ->call('openSetTargetTahunanDivisi')
            ->assertSet('modalSetTargetTahunanDivisiOpen', true)
            ->set('formTargetTahunanLaptop', 50)
            ->set('formTargetTahunanPrinter', 30)
            ->set('formTargetTahunanKomputer', 20)
            ->set('formTargetTahunanAdmin', 40)
            ->call('simpanTargetTahunanDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalSetTargetTahunanDivisiOpen', false);

        // Can edit cell realization
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditCellDivisi', 'laptop', 1)
            ->assertSet('modalEditCellDivisiOpen', true)
            ->set('formRealisasiDivisi', 15)
            ->call('simpanEditCellDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalEditCellDivisiOpen', false);
    }

    public function test_user_with_only_view_permission_cannot_manage_targets()
    {
        $admin = User::role('Admin')->first();
        $admin->givePermissionTo('target.bulanan.view');
        $this->actingAs($admin);

        // Can view page
        $this->get(route('target.bulanan'))->assertStatus(200);

        // canManageTarget is false
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertSet('canManageTarget', false)
            ->call('openSetTargetTahunanDivisi')
            ->assertStatus(403);
    }
}

