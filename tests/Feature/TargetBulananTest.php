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

        // Set target for divisions
        TargetDivisi::updateOrCreate([
            'cabang_id' => $superadmin->cabang_id,
            'divisi' => 'laptop',
            'tahun' => 2026,
        ], [
            'target_bulanan' => 1,
        ]);

        $test = Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026);

        $test->assertViewHas('divisiMatrix', function ($matrix) {
            return isset($matrix['laptop'])
                && $matrix['laptop']['months'][1]['count'] === 1
                && $matrix['laptop']['months'][1]['percent'] === 100
                && $matrix['printer']['months'][2]['count'] === 1
                && $matrix['admin']['months'][3]['count'] === 1;
        });

        $test->assertViewHas('userMatrix', function ($userMatrix) use ($superadmin) {
            $userRow = collect($userMatrix)->firstWhere('user.id', $superadmin->id);
            return $userRow !== null
                && $userRow['months'][1]['count'] === 1
                && $userRow['months'][2]['count'] === 1
                && $userRow['months'][3]['count'] === 1;
        });
    }

    public function test_superadmin_can_save_target_divisi_and_user()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openModalDivisi')
            ->set('targetDivisiForm.laptop', 50)
            ->set('targetDivisiForm.printer', 40)
            ->set('targetDivisiForm.komputer', 30)
            ->set('targetDivisiForm.admin', 60)
            ->call('simpanTargetDivisi')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('target_divisis', [
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'divisi' => 'laptop',
            'tahun' => 2026,
            'target_bulanan' => 50,
        ]);

        $component = Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->call('openModalUser');

        $userForm = $component->get('targetUserForm');
        $this->assertNotEmpty($userForm);

        // Update target for first user
        $userForm[0]['target'] = 25;

        $component->set('targetUserForm', $userForm)
            ->call('simpanTargetUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('target_users', [
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'user_id' => $userForm[0]['user_id'],
            'tahun' => 2026,
            'target_bulanan' => 25,
        ]);
    }
}
