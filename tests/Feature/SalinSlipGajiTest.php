<?php

namespace Tests\Feature;

use App\Livewire\SlipGaji\Form as SlipGajiForm;
use App\Livewire\SlipGaji\Index as SlipGajiIndex;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\SlipGaji;
use App\Models\TarifGaji;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalinSlipGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_salin_dari_bulan_lalu_copies_fixed_values_and_resets_variable_values(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // 1. Buat slip Final bulan September (bulan lalu) dengan nilai khusus
        $slipSeptember = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/09/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2800000,
            'uang_makan_hari' => 20,
            'uang_makan_per_hari' => 25000, // Tarif beda dari default
            'uang_makan_total' => 500000,
            'uang_transport_hari' => 20,
            'uang_transport_per_hari' => 25000,
            'uang_transport_total' => 500000,
            'tunjangan_nama' => 'Tunjangan Khusus',
            'tunjangan_nominal' => 300000,
            'bonus_nominal' => 750000, // Variabel -> TIDAK boleh disalin
            'lembur_performa_nominal' => 150000, // Variabel -> TIDAK boleh disalin
            'servisan_selesai_nominal' => 200000, // Variabel -> TIDAK boleh disalin
            'total_bruto' => 4700000,
            'total_pengurangan' => 0,
            'total_diterima' => 4700000,
            'created_by' => $owner->id,
            'finalized_by' => $owner->id,
            'finalized_at' => now(),
        ]);

        // Buat absensi bulan Oktober (22 hari hadir)
        for ($day = 1; $day <= 22; $day++) {
            Absensi::create([
                'user_id' => $staf->id,
                'cabang_id' => $staf->cabang_id,
                'tanggal' => sprintf('2026-10-%02d', $day),
                'waktu_absen' => sprintf('2026-10-%02d 08:00:00', $day),
                'ip_address' => '127.0.0.1',
                'status_kehadiran' => 'tepat_waktu',
            ]);
        }

        $this->actingAs($owner);

        // Buka form baru untuk Oktober
        $component = Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->call('salinDariBulanLalu');

        // Nilai tetap wajib disalin dari September
        $component->assertSet('gajiPokok', 2800000)
            ->assertSet('uangMakanPerHari', 25000)
            ->assertSet('uangTransportPerHari', 25000)
            ->assertSet('tunjanganNama', 'Tunjangan Khusus')
            ->assertSet('tunjanganNominal', 300000);

        // Nilai variabel wajib NOL
        $component->assertSet('bonusNominal', 0)
            ->assertSet('lemburPerformaNominal', 0)
            ->assertSet('servisanSelesaiNominal', 0);

        // Hari hadir wajib dihitung dari absensi Oktober (22 hari)
        $component->assertSet('uangMakanHari', 22)
            ->assertSet('uangTransportHari', 22);
    }

    public function test_audit_trail_recorded_when_fixed_values_are_modified(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Slip Final September
        $slipSeptember = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/09/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
            'status' => 'final',
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
            'finalized_by' => $owner->id,
            'finalized_at' => now(),
        ]);

        $this->actingAs($owner);

        // Owner mengubah gaji pokok menjadi 3.000.000 (berbeda dari September)
        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('periodeBulan', 10)
            ->set('periodeTahun', 2026)
            ->call('salinDariBulanLalu')
            ->set('gajiPokok', 3000000)
            ->call('saveDraft')
            ->assertHasNoErrors();

        $slipOktober = SlipGaji::where('user_id', $staf->id)->where('periode_bulan', 10)->first();
        $this->assertNotNull($slipOktober);
        $this->assertEquals($owner->id, $slipOktober->diubah_nilai_tetap_by);
        $this->assertNotNull($slipOktober->diubah_nilai_tetap_at);
    }

    public function test_batch_salin_semua_dari_bulan_lalu(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $stafs = User::role(['Teknisi', 'Admin'])->where('cabang_id', 1)->get();

        // Buat slip Final September untuk semua staf
        foreach ($stafs as $idx => $staf) {
            SlipGaji::create([
                'no_slip' => "SLIP/PKP/2026/09/00{$idx}",
                'cabang_id' => 1,
                'user_id' => $staf->id,
                'nama_staf_snapshot' => $staf->name,
                'periode_bulan' => 9,
                'periode_tahun' => 2026,
                'status' => 'final',
                'gaji_pokok' => 2500000 + ($idx * 100000),
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
                'finalized_by' => $owner->id,
                'finalized_at' => now(),
            ]);
        }

        $this->actingAs($owner);

        // Jalankan batch salin semua untuk Oktober
        Livewire::test(SlipGajiIndex::class)
            ->call('openSalinSemuaModal')
            ->call('executeSalinSemua')
            ->assertHasNoErrors();

        foreach ($stafs as $staf) {
            $this->assertDatabaseHas('slip_gajis', [
                'user_id' => $staf->id,
                'periode_bulan' => 10,
                'periode_tahun' => 2026,
                'status' => 'draft',
            ]);
        }
    }
}

