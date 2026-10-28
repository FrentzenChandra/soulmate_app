<?php

namespace Tests\Feature;

use App\Livewire\SlipGaji\Form as SlipGajiForm;
use App\Models\KenaikanGaji;
use App\Models\SlipGaji;
use App\Models\User;
use App\Services\SlipGajiService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KenaikanGajiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_service_evaluates_6_month_milestones_and_skips_older_history(): void
    {
        $service = app(SlipGajiService::class);
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Staf mulai bekerja 18 bulan yang lalu
        $tanggalMulai = Carbon::now('Asia/Jakarta')->subMonths(18)->startOfMonth();
        $staf->update(['tanggal_mulai_bekerja' => $tanggalMulai]);

        $service->evaluasiMilestoneKenaikan($staf->cabang_id);

        // Milestone 6 bulan dan 12 bulan harus berstatus 'dilewati'
        $this->assertDatabaseHas('kenaikan_gajis', [
            'user_id' => $staf->id,
            'milestone_bulan' => 6,
            'status' => 'dilewati',
        ]);

        $this->assertDatabaseHas('kenaikan_gajis', [
            'user_id' => $staf->id,
            'milestone_bulan' => 12,
            'status' => 'dilewati',
        ]);

        // Milestone 18 bulan yang jatuh tempo bulan ini harus berstatus 'menunggu'
        $this->assertDatabaseHas('kenaikan_gajis', [
            'user_id' => $staf->id,
            'milestone_bulan' => 18,
            'status' => 'menunggu',
            'nominal' => 500000,
        ]);
    }

    public function test_terapkan_kenaikan_increases_gaji_pokok(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $kenaikan = KenaikanGaji::create([
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'milestone_bulan' => 6,
            'jatuh_tempo' => Carbon::now('Asia/Jakarta')->toDateString(),
            'nominal' => 500000,
            'status' => 'menunggu',
        ]);

        $this->actingAs($owner);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->set('gajiPokok', 2500000)
            ->assertSet('showKenaikanBanner', true)
            ->call('terapkanKenaikan')
            ->assertSet('gajiPokok', 3000000); // Bertambah 500.000
    }

    public function test_tandai_sudah_ditangani_updates_kenaikan_status(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $kenaikan = KenaikanGaji::create([
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'milestone_bulan' => 6,
            'jatuh_tempo' => Carbon::now('Asia/Jakarta')->toDateString(),
            'nominal' => 500000,
            'status' => 'menunggu',
        ]);

        $this->actingAs($owner);

        Livewire::test(SlipGajiForm::class)
            ->set('selectedUserId', $staf->id)
            ->call('tandaiKenaikanDitangani')
            ->assertSet('showKenaikanBanner', false);

        $kenaikan->refresh();
        $this->assertEquals('ditandai', $kenaikan->status);
        $this->assertEquals($owner->id, $kenaikan->ditangani_by);
        $this->assertNotNull($kenaikan->ditangani_at);
    }
}

