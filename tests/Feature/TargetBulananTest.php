<?php

namespace Tests\Feature;

use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Models\Cabang;
use App\Models\TargetDivisi;
use App\Models\TargetUser;
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
        TargetUser::query()->delete();

        $tahun = 2026;

        // Create TransaksiFull for Laptop in January 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260110-00001',
            'no_transaksi_frontend' => 'PJ-260110-00001',
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Test 1',
            'no_hp' => '08123456789',
            'tipe_transaksi' => 'servis',
            'kategori_perangkat' => 'laptop',
            'tipe_servis' => 'non_real',
            'tanggal' => '2026-01-10',
            'tanggal_kustom' => '2026-01-10',
            'total' => 150000,
            'grand_total' => 150000,
            'status' => 'Selesai',
            'created_at' => '2026-01-10 10:00:00',
        ]);

        // Create TransaksiFull for Printer in February 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260215-00002',
            'no_transaksi_frontend' => 'PJ-260215-00002',
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Test 2',
            'no_hp' => '08123456788',
            'tipe_transaksi' => 'servis',
            'kategori_perangkat' => 'printer',
            'tipe_servis' => 'non_real',
            'tanggal' => '2026-02-15',
            'tanggal_kustom' => '2026-02-15',
            'total' => 200000,
            'grand_total' => 200000,
            'status' => 'Selesai',
            'created_at' => '2026-02-15 10:00:00',
        ]);

        // Create TransaksiFull for Penjualan (Admin division) in March 2026
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260320-00003',
            'no_transaksi_frontend' => 'PJ-260320-00003',
            'cabang_id' => $superadmin->cabang_id,
            'user_id' => $superadmin->id,
            'nama_pelanggan' => 'Pelanggan Test 3',
            'no_hp' => '08123456787',
            'tipe_transaksi' => 'penjualan',
            'tipe_servis' => 'non_real',
            'tanggal' => '2026-03-20',
            'tanggal_kustom' => '2026-03-20',
            'total' => 500000,
            'grand_total' => 500000,
            'status' => 'Lunas',
            'created_at' => '2026-03-20 10:00:00',
        ]);

        // Set monthly targets and realization for divisions
        TargetDivisi::updateOrCreate([
            'cabang_id' => $superadmin->cabang_id,
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

        $test->assertViewHas('userMatrix', function ($userMatrix) use ($superadmin) {
            $userRow = collect($userMatrix)->firstWhere('user.id', $superadmin->id);
            return $userRow !== null
                && $userRow['months'][1]['count'] === 1
                && $userRow['months'][2]['count'] === 1
                && $userRow['months'][3]['count'] === 1;
        });
    }


    public function test_superadmin_can_edit_monthly_target_and_realisasi_divisi()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditBulanDivisi', 5)
            ->assertSet('editBulanDivisi', 5)
            ->set('formTargetSyaratDivisi', 30)
            ->set('formRealisasiLaptop', 45)
            ->set('formRealisasiPrinter', 20)
            ->set('formRealisasiKomputer', 15)
            ->set('formRealisasiAdmin', 10)
            ->call('simpanEditBulanDivisi')
            ->assertHasNoErrors()
            ->assertSet('modalEditBulanDivisiOpen', false);

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

    public function test_superadmin_can_edit_monthly_target_user()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openEditBulanUser', 7)
            ->assertSet('editBulanUser', 7)
            ->set('formTargetSyaratUserGlobal', 20)
            ->call('simpanEditBulanUser')
            ->assertHasNoErrors()
            ->assertSet('modalEditBulanUserOpen', false);

        $targetUser = TargetUser::where('cabang_id', $superadmin->cabang_id ?: 1)
            ->where('user_id', $superadmin->id)
            ->where('tahun', 2026)
            ->first();

        $this->assertNotNull($targetUser);
        $this->assertEquals(20, $targetUser->monthly_targets['7']);

        // Test rendering reflection
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('userMatrix', function ($userMatrix) use ($superadmin) {
                $userRow = collect($userMatrix)->firstWhere('user.id', $superadmin->id);
                return $userRow !== null
                    && $userRow['months'][7]['target'] === 20;
            });
    }
}

