<?php

namespace Tests\Feature;

use App\Livewire\SlipGaji\Form as SlipGajiForm;
use App\Livewire\SlipGaji\Index as SlipGajiIndex;
use App\Models\Cabang;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use App\Models\TarifGaji;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SlipGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_access_slip_gaji_pages(): void
    {
        $this->get(route('slip-gaji.index'))->assertRedirect(route('login'));
        $this->get(route('slip-gaji.create'))->assertRedirect(route('login'));
    }

    public function test_owner_and_superadmin_can_access_slip_gaji_index(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);
        $this->get(route('slip-gaji.index'))->assertOk();

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);
        $this->get(route('slip-gaji.index'))->assertOk();
    }

    public function test_index_component_renders_and_filters(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Create dummy slip
        $slip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 2500000,
            'uang_makan_hari' => 26,
            'uang_makan_per_hari' => 20000,
            'uang_makan_total' => 520000,
            'uang_transport_hari' => 26,
            'uang_transport_per_hari' => 20000,
            'uang_transport_total' => 520000,
            'total_bruto' => 3540000,
            'total_pengurangan' => 0,
            'total_diterima' => 3540000,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner);

        Livewire::test(SlipGajiIndex::class)
            ->assertOk()
            ->set('search', 'teknisi')
            ->assertSee($staf->name)
            ->call('setPeriodeMode', 'bulanan')
            ->assertSet('periodeMode', 'bulanan');
    }

    public function test_save_draft_slip_gaji(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($owner);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 3000000)
            ->set('bonusNominal', 500000)
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        $this->assertDatabaseHas('slip_gajis', [
            'user_id' => $staf->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 3000000,
            'bonus_nominal' => 500000,
        ]);
    }

    public function test_server_side_guard_for_gaji_pokok_edit_permission(): void
    {
        // Staf dengan izin gaji.create tapi TANPA gaji.pokok.edit
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Buat slip Final sumber dengan gaji_pokok 2.500.000
        $sourceSlip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/09/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2500000,
            'uang_makan_hari' => 25,
            'uang_makan_per_hari' => 20000,
            'uang_makan_total' => 500000,
            'uang_transport_hari' => 25,
            'uang_transport_per_hari' => 20000,
            'uang_transport_total' => 500000,
            'total_bruto' => 3500000,
            'total_pengurangan' => 0,
            'total_diterima' => 3500000,
            'created_by' => 1,
            'finalized_by' => 1,
            'finalized_at' => now(),
        ]);

        $this->actingAs($admin);

        // Coba manipulasi nilai gajiPokok menjadi 5.000.000 dari form
        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 5000000) // Nilai suntikan ilegal
            ->set('uangMakanPerHari', 50000)
            ->call('saveDraft')
            ->assertHasNoErrors();

        // Server harus mengabaikan 5.000.000 dan tetap menggunakan nilai sumber 2.500.000
        $this->assertDatabaseHas('slip_gajis', [
            'user_id' => $staf->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'gaji_pokok' => 2500000,
            'uang_makan_per_hari' => 20000,
        ]);

        $this->assertDatabaseMissing('slip_gajis', [
            'user_id' => $staf->id,
            'periode_bulan' => 10,
            'gaji_pokok' => 5000000,
        ]);
    }

    public function test_finalize_freezes_snapshot_and_prevents_further_edits(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $staf->update([
            'bagian' => 'Teknisi Laptop',
            'tanggal_lahir' => '2000-04-30',
            'tanggal_mulai_bekerja' => '2022-10-01',
        ]);

        $this->actingAs($owner);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 3000000)
            ->call('finalize')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        $slip = SlipGaji::where('user_id', $staf->id)->where('periode_bulan', 10)->first();
        $this->assertNotNull($slip);
        $this->assertEquals('final', $slip->status);
        $this->assertEquals('Teknisi Laptop', $slip->bagian_snapshot);
        $this->assertEquals($staf->name, $slip->nama_staf_snapshot);
        $this->assertNotNull($slip->finalized_at);
        $this->assertNotNull($slip->finalized_by);
    }

    public function test_owner_can_reopen_finalized_slip(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $slip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/002',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 3000000,
            'uang_makan_hari' => 26,
            'uang_makan_per_hari' => 20000,
            'uang_makan_total' => 520000,
            'uang_transport_hari' => 26,
            'uang_transport_per_hari' => 20000,
            'uang_transport_total' => 520000,
            'total_bruto' => 4040000,
            'total_pengurangan' => 0,
            'total_diterima' => 4040000,
            'created_by' => $owner->id,
            'finalized_by' => $owner->id,
            'finalized_at' => now(),
        ]);

        $this->actingAs($owner);

        Livewire::test(SlipGajiIndex::class)
            ->call('confirmReopen', $slip->id, $slip->no_slip)
            ->set('alasanBukaKembali', 'Koreksi uang makan karena lupa input absensi')
            ->call('executeReopen')
            ->assertHasNoErrors();

        $slip->refresh();
        $this->assertEquals('draft', $slip->status);
        $this->assertStringContainsString('Koreksi uang makan', $slip->alasan_buka_kembali);
        $this->assertEquals($owner->id, $slip->reopened_by);
        $this->assertNotNull($slip->reopened_at);
    }

    public function test_custom_penerimaan_tambahan_and_custom_pengurangan_without_revert_bug(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($owner);

        $test = Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->set('gajiPokok', 2500000)
            ->set('uangMakanHari', 20)
            ->set('uangMakanPerHari', 20000)
            ->set('uangTransportHari', 20)
            ->set('uangTransportPerHari', 20000)
            // 1. Tambah penerimaan tambahan custom (Servisan Selesai, Bonus, Lembur)
            ->call('tambahPenerimaan')
            ->set('penerimaanItems.0.nama_item', 'Servisan Selesai Laptop Acer')
            ->set('penerimaanItems.0.nominal', 250000)
            ->call('tambahPenerimaan')
            ->set('penerimaanItems.1.nama_item', 'Bonus Target Toko')
            ->set('penerimaanItems.1.nominal', 100000)
            // 2. Tambah potongan custom dan buktikan nama TIDAK revert kembali ke kategori
            ->call('tambahPotongan')
            ->set('penguranganItems.0.nama_item', 'Hutang Pulsa Kantor')
            ->set('penguranganItems.0.nominal', 50000);

        // Pastikan nama_item tetap 'Hutang Pulsa Kantor', tidak tertimpa nama semula
        $test->assertSet('penguranganItems.0.nama_item', 'Hutang Pulsa Kantor');
        $test->assertSet('penerimaanItems.0.nama_item', 'Servisan Selesai Laptop Acer');
        $test->assertSet('penerimaanItems.1.nama_item', 'Bonus Target Toko');

        // Verifikasi kalkulasi:
        // Gaji Pokok: 2.500.000
        // Uang Makan: 400.000
        // Uang Transport: 400.000
        // Penerimaan Tambahan (Servisan Selesai: 250.000 + Bonus: 100.000) = 350.000
        // Total Bruto = 3.650.000
        // Total Pengurangan = 50.000
        // Total Diterima = 3.600.000
        $test->assertSet('totalBruto', 3650000.0)
            ->assertSet('totalPengurangan', 50000.0)
            ->assertSet('totalDiterima', 3600000.0)
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        // Cek database slip_gajis
        $slip = SlipGaji::where('user_id', $staf->id)
            ->where('periode_bulan', 10)
            ->where('periode_tahun', 2026)
            ->first();

        $this->assertNotNull($slip);
        $this->assertEquals(3650000, (float) $slip->total_bruto);
        $this->assertEquals(50000, (float) $slip->total_pengurangan);
        $this->assertEquals(3600000, (float) $slip->total_diterima);

        // Cek database slip_gaji_items: penerimaan & pengurangan tersimpan presisi
        $this->assertDatabaseHas('slip_gaji_items', [
            'slip_gaji_id' => $slip->id,
            'tipe' => 'penerimaan',
            'nama_item' => 'Servisan Selesai Laptop Acer',
            'nominal' => 250000,
        ]);

        $this->assertDatabaseHas('slip_gaji_items', [
            'slip_gaji_id' => $slip->id,
            'tipe' => 'penerimaan',
            'nama_item' => 'Bonus Target Toko',
            'nominal' => 100000,
        ]);

        $this->assertDatabaseHas('slip_gaji_items', [
            'slip_gaji_id' => $slip->id,
            'tipe' => 'pengurangan',
            'nama_item' => 'Hutang Pulsa Kantor',
            'nominal' => 50000,
        ]);
    }

    public function test_create_page_does_not_redirect_when_first_staff_already_has_slip(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf1 = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($owner);

        // Buat slip gaji untuk staf1 pada bulan berjalan
        $now = Carbon::now('Asia/Jakarta');
        SlipGaji::create([
            'no_slip' => 'SLIP/PKP/EXISTING/001',
            'cabang_id' => $staf1->cabang_id,
            'user_id' => $staf1->id,
            'nama_staf_snapshot' => $staf1->name,
            'periode_bulan' => (int) $now->month,
            'periode_tahun' => (int) $now->year,
            'status' => 'draft',
            'gaji_pokok' => 2500000,
            'uang_makan_hari' => 20,
            'uang_makan_per_hari' => 20000,
            'uang_makan_total' => 400000,
            'uang_transport_hari' => 20,
            'uang_transport_per_hari' => 20000,
            'uang_transport_total' => 400000,
            'total_bruto' => 3300000,
            'total_pengurangan' => 0,
            'total_diterima' => 3300000,
            'created_by' => $owner->id,
        ]);

        // Saat buka halaman /slip-gaji/buat, response HARUS OK (200), TIDAK redirect (302) ke slip-gaji.edit
        $response = $this->get(route('slip-gaji.create'));
        $response->assertOk();

        // Uji komponen Livewire: tidak me-redirect dan mendeteksi existing slip dengan tepat jika staf1 dipilih
        $test = Livewire::test(SlipGajiForm::class)
            ->assertOk()
            ->set('selectedUserId', $staf1->id);

        $test->assertSet('existingSlipId', fn($val) => ! empty($val))
            ->assertSee('Karyawan ini telah memiliki slip gaji');
    }

    public function test_blank_default_values_and_cursor_compatibility_for_slip_gaji(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        // Buat staf baru yang belum punya riwayat slip gaji
        $newStaf = User::factory()->create([
            'cabang_id' => $owner->cabang_id,
            'status' => 'aktif',
            'bagian' => 'Teknisi Printer',
        ]);
        $newStaf->assignRole('Teknisi');

        $this->actingAs($owner);

        // Uji inisialisasi staf baru: gajiPokok harus null (blank), bukan 0
        $test = Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $newStaf->id)
            ->set('periodeBulan', 11)
            ->set('periodeTahun', 2026);

        $test->assertSet('gajiPokok', null);

        // Uji tambah penerimaan & potongan baru: nominal default harus '' (blank), bukan 0
        $test->call('tambahPenerimaan')
            ->assertSet('penerimaanItems.0.nominal', '')
            ->call('tambahPotongan')
            ->assertSet('penguranganItems.0.nominal', '');

        // Uji bahwa nilai blank/empty string tidak menyebabkan error pada kalkulasi total
        $test->assertSet('totalBruto', fn($val) => is_numeric($val))
            ->assertSet('totalPengurangan', 0.0)
            ->assertSet('totalDiterima', fn($val) => is_numeric($val));

        // Uji validasi finalize: gaji pokok kosong/nol harus ditolak
        $test->call('finalize');
        $this->assertDatabaseMissing('slip_gajis', [
            'user_id' => $newStaf->id,
            'periode_bulan' => 11,
            'periode_tahun' => 2026,
            'status' => 'final',
        ]);

        // Uji pengisian nominal dan penyimpanan draft
        $test->set('gajiPokok', 2800000)
            ->set('penerimaanItems.0.nama_item', 'Lembur Mingguan')
            ->set('penerimaanItems.0.nominal', 150000)
            ->set('penguranganItems.0.nama_item', 'Terlambat')
            ->set('penguranganItems.0.nominal', 20000)
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('slip-gaji.index'));

        $this->assertDatabaseHas('slip_gajis', [
            'user_id' => $newStaf->id,
            'periode_bulan' => 11,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 2800000,
        ]);
    }
}

