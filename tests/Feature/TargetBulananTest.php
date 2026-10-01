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

    public function test_superadmin_can_set_annual_target_for_divisi()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openSetTargetTahunanDivisi', 'laptop')
            ->assertSet('formDivisiKey', 'laptop')
            ->assertSet('modalSetTargetTahunanDivisiOpen', true)
            ->set('formTargetTahunanDivisi', 120)
            ->call('simpanTargetTahunanDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalSetTargetTahunanDivisiOpen', false);

        $target = TargetDivisi::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('divisi', 'laptop')
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($target);
        $this->assertEquals(10, $target->target_bulanan);
        $this->assertEquals(10, $target->monthly_targets['1']);
        $this->assertEquals(10, $target->monthly_targets['12']);

        // Verify reflection in divisiMatrix
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return isset($divisiMatrix['laptop'])
                    && $divisiMatrix['laptop']['total_target_year'] === 120
                    && $divisiMatrix['laptop']['months'][1]['target'] === 10
                    && $divisiMatrix['laptop']['months'][12]['target'] === 10;
            });
    }

    public function test_superadmin_can_edit_monthly_custom_target_and_realisasi_divisi()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditCellDivisi', 'printer', 5)
            ->assertSet('editDivisiKey', 'printer')
            ->assertSet('editDivisiBulan', 5)
            ->set('formTargetSyaratDivisi', 25)
            ->set('formRealisasiDivisi', 30)
            ->call('simpanEditCellDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalEditCellDivisiOpen', false);

        $targetDivisi = TargetDivisi::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('divisi', 'printer')
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($targetDivisi);
        $this->assertEquals(25, $targetDivisi->monthly_targets['5']);
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
}
