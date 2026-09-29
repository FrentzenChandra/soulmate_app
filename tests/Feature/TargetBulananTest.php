<?php

namespace Tests\Feature;

use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
use App\Models\TargetPegawai;
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

    public function test_division_realization_matrix_calculated_from_transaksi_full()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        TransaksiFull::query()->delete();
        TargetDivisi::query()->delete();
        TargetPegawai::query()->delete();

        $tahun = 2026;

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

        // Set monthly targets and realization for divisions
        TargetDivisi::updateOrCreate([
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'divisi' => 'laptop',
            'tahun' => 2026,
        ], [
            'target_bulanan' => 10,
            'monthly_targets' => ['1' => 10],
            'monthly_realisasi' => ['1' => 10],
        ]);

        $test = Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026);

        $test->assertViewHas('divisiMatrix', function ($matrix) {
            return isset($matrix['laptop'])
                && $matrix['laptop']['months'][1]['count'] === 10
                && $matrix['laptop']['months'][1]['target'] === 10
                && $matrix['laptop']['months'][1]['percent'] === 100
                && $matrix['printer']['months'][2]['has_data'] === false;
        });

        $test->assertViewHas('userMatrix', function ($userMatrix) use ($pegawai) {
            $pegawaiRow = collect($userMatrix)->firstWhere('user.id', $pegawai->id);
            return $pegawaiRow !== null
                && $pegawaiRow['months'][1]['count'] === 1
                && $pegawaiRow['months'][2]['count'] === 1
                && $pegawaiRow['months'][3]['count'] === 1;
        });
    }

    public function test_superadmin_can_edit_monthly_target_and_realisasi_divisi()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditCellDivisi', 'laptop', 5)
            ->assertSet('editDivisiKey', 'laptop')
            ->assertSet('editDivisiBulan', 5)
            ->set('formTargetSyaratDivisi', 30)
            ->set('formRealisasiDivisi', 45)
            ->call('simpanEditCellDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalEditCellDivisiOpen', false);

        $target = TargetDivisi::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('divisi', 'laptop')
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($target);
        $this->assertEquals(30, $target->monthly_targets['5']);
        $this->assertEquals(45, $target->monthly_realisasi['5']);

        // Test rendering reflection
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($matrix) {
                return isset($matrix['laptop'])
                    && $matrix['laptop']['months'][5]['target'] === 30
                    && $matrix['laptop']['months'][5]['count'] === 45
                    && $matrix['laptop']['months'][5]['percent'] === 150
                    && $matrix['laptop']['months'][5]['status'] === 'over';
            });
    }

    public function test_superadmin_can_edit_monthly_target_pegawai()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $pegawai = Pegawai::firstOrCreate(
            ['cabang_id' => $superadmin->cabang_id ?: 1, 'nama' => 'Teknisi Test'],
            ['role' => 'Teknisi', 'is_aktif' => true]
        );

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditCellPegawai', $pegawai->id, 7)
            ->assertSet('editUserId', $pegawai->id)
            ->assertSet('editUserBulan', 7)
            ->set('formTargetSyaratUser', 20)
            ->call('simpanEditCellPegawai')
            ->assertHasNoErrors()
            ->assertSet('modalEditCellUserOpen', false);

        $targetPegawai = TargetPegawai::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('pegawai_id', $pegawai->id)
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($targetPegawai);
        $this->assertEquals(20, $targetPegawai->monthly_targets['7']);

        // Test rendering reflection
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('userMatrix', function ($userMatrix) use ($pegawai) {
                $pegawaiRow = collect($userMatrix)->firstWhere('user.id', $pegawai->id);
                return $pegawaiRow !== null
                    && $pegawaiRow['months'][7]['target'] === 20
                    && $pegawaiRow['months'][7]['has_data'] === true
                    && $pegawaiRow['months'][8]['has_data'] === false
                    && $pegawaiRow['months'][8]['target'] === 0;
            });
    }
}
