<?php

namespace Tests\Feature\Menu;

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class DashboardTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin berhak akses' => ['Admin', 200],
            'Teknisi berhak akses' => ['Teknisi', 200],
            'Bendahara berhak akses' => ['Bendahara', 200],
            'Operasional berhak akses' => ['Operasional', 200],
            'Magang berhak akses' => ['Magang', 200],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_dashboard_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('dashboard'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_render_dashboard_untuk_staf_operasional(): void
    {
        $admin = $this->actingAsRole('Admin');

        Livewire::test(DashboardIndex::class)
            ->assertStatus(200)
            ->assertViewHas('isManagement', false)
            ->assertViewHas('isOwner', false);
    }

    public function test_render_dashboard_untuk_superadmin_cabang(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $customer = \App\Models\Customer::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Pelanggan Dashboard',
            'tipe' => 'langganan',
        ]);

        ServisMasuk::create([
            'cabang_id' => $superadmin->cabang_id,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'no_servis' => 'INV-SV-DASH-01',
            'tanggal_masuk' => now()->toDateString(),
            'tipe_layanan' => 'reguler',
            'status' => 'pengecekan',
            'tipe_perangkat' => 'Laptop',
            'nama_unit' => 'Acer Aspire',
            'keluhan' => 'Mati Total',
            'is_real' => true,
        ]);

        Livewire::test(DashboardIndex::class)
            ->assertStatus(200)
            ->assertViewHas('isManagement', true)
            ->assertViewHas('isSuperadmin', true)
            ->assertViewHas('isOwner', false);
    }

    public function test_render_dashboard_untuk_owner_hq_dan_switcher_cabang(): void
    {
        $this->actingAsRole('Owner');

        $cabang2 = Cabang::create([
            'kode_cabang' => 'CB-2',
            'nama_cabang' => 'Cabang Palembang 2',
            'alamat' => 'Jl. Sudirman',
            'no_wa' => '0812345678',
            'status' => 'aktif',
        ]);

        // Dashboard HQ Aggregate
        Livewire::test(DashboardIndex::class)
            ->assertStatus(200)
            ->assertViewHas('isManagement', true)
            ->assertViewHas('isOwner', true)
            ->assertViewHas('isHQ', true);

        // Switch context to branch
        Livewire::test(DashboardIndex::class)
            ->call('switchToBranch', $cabang2->id)
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($cabang2->id, session('active_branch_id'));

        // Switch context back to HQ
        Livewire::test(DashboardIndex::class)
            ->call('switchToHq')
            ->assertRedirect(route('dashboard'));

        $this->assertEquals('hq', session('active_branch_id'));
    }

    public function test_refresh_dashboard_clear_cache(): void
    {
        $this->actingAsRole('Superadmin');

        Livewire::test(DashboardIndex::class)
            ->call('refreshDashboard')
            ->assertDispatched('swal:toast');
    }
}

