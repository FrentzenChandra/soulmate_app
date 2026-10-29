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
}

