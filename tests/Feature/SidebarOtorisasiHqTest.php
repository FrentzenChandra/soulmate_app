<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class SidebarOtorisasiHqTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public function test_admin_in_hq_does_not_see_saldo_and_master_pegawai_menu(): void
    {
        $admin = $this->actingAsRole('Admin');

        // Buka dashboard dengan sesi HQ (jika ada)
        $response = $this->withSession(['active_branch_id' => 'hq'])->get(route('dashboard'));

        $response->assertStatus(200);
        // Pastikan link route saldo dan master pegawai TIDAK ADA di sidebar
        $response->assertDontSee(route('saldo.index'));
        $response->assertDontSee(route('master.pegawai'));

        // Akses langsung via route wajib 403 Forbidden
        $this->get(route('saldo.index'))->assertStatus(403);
        $this->get(route('master.pegawai'))->assertStatus(403);
    }

    public function test_superadmin_sees_saldo_target_pegawai_and_master_pegawai_and_can_access_200(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        // Pastikan menu Saldo, Target Pegawai, dan Master Pegawai MUNCUL
        $response->assertSee(route('saldo.index'));
        $response->assertSee(route('target.bulanan'));
        $response->assertSee(route('master.pegawai'));

        // Akses langsung via route wajib 200 OK
        $this->get(route('saldo.index'))->assertStatus(200);
        $this->get(route('target.bulanan'))->assertStatus(200);
        $this->get(route('master.pegawai'))->assertStatus(200);
    }

    public function test_owner_in_hq_sees_all_menus_completely(): void
    {
        $owner = $this->actingAsRole('Owner');

        // Owner di konteks HQ
        $response = $this->withSession(['active_branch_id' => 'hq'])->get(route('dashboard'));

        $response->assertStatus(200);
        // Pastikan header tata kelola HQ dan menu-menu operasional muncul lengkap
        $response->assertSee('Tata Kelola Pusat');
        $response->assertSee(route('kas-keluar'));
        $response->assertSee(route('saldo.index'));
        $response->assertSee(route('target.bulanan'));
        $response->assertSee(route('serial-number'));

        // Menu dalam dropdown setting HQ
        $response->assertSee(route('master.customer'));
        $response->assertSee(route('master.users'));
        $response->assertSee(route('master.servis'));
        $response->assertSee(route('master.pegawai'));
        $response->assertSee(route('master.kategori'));
        $response->assertSee(route('master.metode-pembayaran'));
        $response->assertSee(route('master.sumber-dana'));
        $response->assertSee(route('master.kategori-kas'));
        $response->assertSee(route('master.distributor'));
        $response->assertSee(route('master.wifi-absen'));

        // Menu absensi dan gaji
        $response->assertSee(route('slip-gaji.index'));

        // Akses langsung via route wajib 200 OK
        $this->get(route('saldo.index'))->assertStatus(200);
        $this->get(route('target.bulanan'))->assertStatus(200);
        $this->get(route('master.pegawai'))->assertStatus(200);
        $this->get(route('master.kategori'))->assertStatus(200);
        $this->get(route('master.metode-pembayaran'))->assertStatus(200);
        $this->get(route('master.sumber-dana'))->assertStatus(200);
        $this->get(route('master.kategori-kas'))->assertStatus(200);
        $this->get(route('slip-gaji.index'))->assertStatus(200);
    }

    public function test_hq_sidebar_blade_directly_blocks_admin_from_seeing_saldo_and_pegawai(): void
    {
        // Simulasi jika user Admin me-render layout dengan context isHQ = true secara eksplisit
        $admin = $this->actingAsRole('Admin');

        // Render blade component / layout secara langsung dengan user Admin
        $view = $this->actingAs($admin)->blade(
            '@include("layouts.app", ["slot" => "<div>Content</div>"])'
        );

        // Di layout mana pun, Admin tidak boleh melihat route saldo & pegawai
        $this->assertStringNotContainsString(route('saldo.index'), (string) $view);
        $this->assertStringNotContainsString(route('master.pegawai'), (string) $view);
    }
}

