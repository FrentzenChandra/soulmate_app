<?php

namespace Tests\Feature;

use App\Livewire\Absensi\Riwayat as RiwayatIndex;
use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RiwayatAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_access_riwayat_absensi(): void
    {
        $this->get(route('absensi.riwayat'))->assertRedirect(route('login'));
    }

    public function test_staff_only_sees_their_own_attendance(): void
    {
        $teknisiPkp = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $adminPkp = User::where('email', 'admin.pkp@soulmate.test')->first();

        // Absensi teknisi PKP
        Absensi::create([
            'cabang_id' => $teknisiPkp->cabang_id,
            'user_id' => $teknisiPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        // Absensi admin PKP
        Absensi::create([
            'cabang_id' => $adminPkp->cabang_id,
            'user_id' => $adminPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($teknisiPkp);

        Livewire::test(RiwayatIndex::class)
            ->assertSee($teknisiPkp->name)
            ->assertDontSee($adminPkp->name)
            ->assertDontSee('Semua Cabang'); // Non-owner cannot see branch filter
    }

    public function test_superadmin_sees_branch_attendance_without_branch_filter(): void
    {
        $superadminPkp = User::where('email', 'superadmin@soulmate.test')->first();
        $teknisiPkp = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $teknisiSgl = User::where('email', 'teknisi.sgl@soulmate.test')->first();

        Absensi::create([
            'cabang_id' => $teknisiPkp->cabang_id,
            'user_id' => $teknisiPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        Absensi::create([
            'cabang_id' => $teknisiSgl->cabang_id,
            'user_id' => $teknisiSgl->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($superadminPkp);

        Livewire::test(RiwayatIndex::class)
            ->assertSee($teknisiPkp->name)
            ->assertDontSee($teknisiSgl->name)
            ->assertDontSee('Semua Cabang'); // Branch obfuscation for non-owner
    }

    public function test_owner_sees_all_branches_and_can_filter_by_branch(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $teknisiPkp = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $teknisiSgl = User::where('email', 'teknisi.sgl@soulmate.test')->first();

        Absensi::create([
            'cabang_id' => $teknisiPkp->cabang_id,
            'user_id' => $teknisiPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        Absensi::create([
            'cabang_id' => $teknisiSgl->cabang_id,
            'user_id' => $teknisiSgl->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($owner);

        Livewire::test(RiwayatIndex::class)
            ->assertSee('Semua Cabang')
            ->assertSee($teknisiPkp->name)
            ->assertSee($teknisiSgl->name)
            ->set('cabangFilter', (string) $teknisiPkp->cabang_id)
            ->assertSee($teknisiPkp->name)
            ->assertDontSee($teknisiSgl->name);
    }

    public function test_multi_word_search_filters_by_name(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $teknisiPkp = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $adminPkp = User::where('email', 'admin.pkp@soulmate.test')->first();

        Absensi::create([
            'cabang_id' => $teknisiPkp->cabang_id,
            'user_id' => $teknisiPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        Absensi::create([
            'cabang_id' => $adminPkp->cabang_id,
            'user_id' => $adminPkp->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($owner);

        Livewire::test(RiwayatIndex::class)
            ->set('search', 'budi pkp')
            ->assertSee($teknisiPkp->name)
            ->assertDontSee($adminPkp->name);
    }

    public function test_period_filter_modes(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Past record (1 month ago)
        Absensi::create([
            'cabang_id' => $user->cabang_id,
            'user_id' => $user->id,
            'tanggal' => now()->subMonths(2)->startOfMonth()->toDateString(),
            'waktu_absen' => now()->subMonths(2),
            'ip_address' => '127.0.0.1',
        ]);

        // Today's record
        Absensi::create([
            'cabang_id' => $user->cabang_id,
            'user_id' => $user->id,
            'tanggal' => now()->toDateString(),
            'waktu_absen' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($owner);

        // Mode Hari Ini should only show 1 record
        $component = Livewire::test(RiwayatIndex::class)
            ->set('periodeMode', 'hari_ini')
            ->set('startDate', now()->toDateString())
            ->set('endDate', now()->toDateString());

        $this->assertEquals(1, $component->viewData('filteredCount'));

        // Mode Semua should show 2 records
        $component->call('setPeriodeMode', 'semua');
        $this->assertEquals(2, $component->viewData('filteredCount'));
    }

    public function test_infinite_scroll_load_more_increments_per_page(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $component = Livewire::test(RiwayatIndex::class);
        $this->assertEquals(15, $component->get('perPage'));

        $component->call('loadMore');
        $this->assertEquals(30, $component->get('perPage'));

        // Reset on mode change
        $component->call('setPeriodeMode', 'semua');
        $this->assertEquals(15, $component->get('perPage'));
    }

    public function test_infinite_loading_ui_presence(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $user = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        // Create 20 records on different dates so hasMorePages is true (> 15)
        for ($i = 0; $i < 20; $i++) {
            Absensi::create([
                'cabang_id' => $user->cabang_id,
                'user_id' => $user->id,
                'tanggal' => now()->subDays($i)->toDateString(),
                'waktu_absen' => now()->subDays($i)->setTime(8, 0, 0),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $this->actingAs($owner);

        Livewire::test(RiwayatIndex::class)
            ->call('setPeriodeMode', 'semua')
            ->assertSee('wire:intersect="loadMore"', false)
            ->assertSee('Memuat riwayat kehadiran berikutnya...', false)
            ->assertSee('#5E17EB', false);
    }
}

