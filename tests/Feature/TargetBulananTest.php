<?php

namespace Tests\Feature;

use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\Pegawai;
use App\Models\ServisMasuk;
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

    public function test_admin_division_auto_calculated_from_transaksi_full_penjualan_lunas_by_tanggal_lunas()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        TransaksiFull::query()->delete();
        TargetDivisi::query()->delete();

        $cabangId = $superadmin->cabang_id ?: 1;

        // 1. Setup annual target for admin division
        TargetDivisi::updateOrCreate(
            ['cabang_id' => $cabangId, 'divisi' => 'admin', 'tahun' => 2026],
            ['target_bulanan' => 10]
        );

        // 2. Transaksi Penjualan Lunas in January 2026 -> SHOULD BE COUNTED in Month 1
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260110-00010',
            'cabang_id' => $cabangId,
            'nama_pelanggan' => 'Pelanggan Lunas Jan',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => '2026-01-10 10:00:00',
            'tanggal_lunas' => '2026-01-10 10:00:00',
            'total_modal' => 50000,
            'total_jual' => 100000,
            'grand_total' => 100000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        // 3. Transaksi Penjualan BON in January 2026 -> SHOULD NOT BE COUNTED
        $bonTrx = TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260115-00011',
            'cabang_id' => $cabangId,
            'nama_pelanggan' => 'Pelanggan Bon Jan',
            'tipe_transaksi' => 'penjualan',
            'tanggal' => '2026-01-15 11:00:00',
            'tanggal_lunas' => null,
            'total_modal' => 80000,
            'total_jual' => 150000,
            'grand_total' => 150000,
            'status' => 'Bon',
            'metode_pembayaran' => 'Cash',
        ]);

        // 4. Transaksi Servis Lunas in January 2026 -> SHOULD NOT BE COUNTED in Divisi Admin
        TransaksiFull::create([
            'no_transaksi' => 'INV-PJ-PKP-260120-00012',
            'cabang_id' => $cabangId,
            'nama_pelanggan' => 'Pelanggan Servis Jan',
            'tipe_transaksi' => 'servis',
            'tanggal' => '2026-01-20 14:00:00',
            'tanggal_lunas' => '2026-01-20 14:00:00',
            'total_modal' => 30000,
            'total_jual' => 75000,
            'grand_total' => 75000,
            'status' => 'Lunas',
            'metode_pembayaran' => 'Cash',
        ]);

        // Verify January has count 1 (only the paid sales transaction)
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return isset($divisiMatrix['admin'])
                    && $divisiMatrix['admin']['months'][1]['count'] === 1
                    && $divisiMatrix['admin']['months'][2]['count'] === 0;
            });

        // 5. Now settle the BON transaction in February 2026 (tanggal_lunas = 2026-02-14)
        $bonTrx->update([
            'status' => 'Lunas',
            'tanggal_lunas' => '2026-02-14 15:00:00',
        ]);

        // Verify February now has count 1, and January remains 1
        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return isset($divisiMatrix['admin'])
                    && $divisiMatrix['admin']['months'][1]['count'] === 1
                    && $divisiMatrix['admin']['months'][2]['count'] === 1
                    && $divisiMatrix['admin']['total_ytd'] === 2;
            });
    }

    public function test_technical_divisions_auto_calculated_from_servis_masuk_by_tanggal_ambil()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        ServisMasuk::query()->delete();
        TargetDivisi::query()->delete();
        TransaksiFull::query()->delete();

        $cabangId = $superadmin->cabang_id ?: 1;
        $customer = Customer::create([
            'cabang_id' => $cabangId,
            'nama' => 'Pelanggan Servis Test',
            'no_hp' => '08123456789',
        ]);

        // 1. Laptop taken in January 2026 (status: sudah_diambil) -> SHOULD BE COUNTED
        ServisMasuk::create([
            'no_servis' => 'SRV-LP-001',
            'cabang_id' => $cabangId,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus ROG',
            'keluhan' => 'Layar blank',
            'status' => 'sudah_diambil',
            'tanggal_masuk' => '2026-01-05 10:00:00',
            'tanggal_ambil' => '2026-01-15 14:00:00',
        ]);

        // 2. Laptop with status 'batal' taken in January 2026 -> SHOULD NOT BE COUNTED
        ServisMasuk::create([
            'no_servis' => 'SRV-LP-002',
            'cabang_id' => $cabangId,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Thinkpad',
            'keluhan' => 'Mati total',
            'status' => 'batal',
            'tanggal_masuk' => '2026-01-08 10:00:00',
            'tanggal_ambil' => '2026-01-20 11:00:00',
        ]);

        // 3. Laptop not yet taken (tanggal_ambil IS NULL) -> SHOULD NOT BE COUNTED
        ServisMasuk::create([
            'no_servis' => 'SRV-LP-003',
            'cabang_id' => $cabangId,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Acer Swift',
            'keluhan' => 'Keyboard rusak',
            'status' => 'selesai',
            'tanggal_masuk' => '2026-01-10 10:00:00',
            'tanggal_ambil' => null,
        ]);

        // 4. Printer taken in February 2026 -> SHOULD BE COUNTED in month 2
        ServisMasuk::create([
            'no_servis' => 'SRV-PR-001',
            'cabang_id' => $cabangId,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L3110',
            'keluhan' => 'Tinta mampet',
            'status' => 'sudah_diambil',
            'tanggal_masuk' => '2026-02-01 10:00:00',
            'tanggal_ambil' => '2026-02-10 16:00:00',
        ]);

        // 5. Komputer/PC taken in March 2026 -> SHOULD BE COUNTED in month 3
        ServisMasuk::create([
            'no_servis' => 'SRV-PC-001',
            'cabang_id' => $cabangId,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Rakitan Core i7',
            'keluhan' => 'Upgrade RAM & SSD',
            'status' => 'sudah_diambil',
            'tanggal_masuk' => '2026-03-01 10:00:00',
            'tanggal_ambil' => '2026-03-05 15:00:00',
        ]);

        Livewire::test(TargetBulananIndex::class)
            ->set('filterTahun', 2026)
            ->assertViewHas('divisiMatrix', function ($divisiMatrix) {
                return $divisiMatrix['laptop']['months'][1]['count'] === 1
                    && $divisiMatrix['laptop']['months'][2]['count'] === 0
                    && $divisiMatrix['printer']['months'][2]['count'] === 1
                    && $divisiMatrix['komputer']['months'][3]['count'] === 1
                    && $divisiMatrix['admin']['months'][1]['count'] === 0; // Admin is manual, zero by default
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

        $this->assertDatabaseHas('target_divisis', [
            'cabang_id' => $teknisi->cabang_id ?: 1,
            'tahun' => 2026,
            'divisi' => 'admin',
            'target_bulanan' => 40,
        ]);
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

