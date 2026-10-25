<?php

namespace Tests\Feature\Menu;

use App\Livewire\TutupKas\Index as TutupKasIndex;
use App\Models\Cabang;
use App\Models\TutupKas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class TutupKasTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Cabang Pangkalpinang',
            'kode_cabang' => 'CB-PKP',
            'alamat' => 'Jl. Merdeka No. 12',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('tutup-kas'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Bendahara is forbidden (mount check)' => ['Bendahara', 403],
            'Teknisi is forbidden (middleware check)' => ['Teknisi', 403],
            'Operasional is forbidden (middleware check)' => ['Operasional', 403],
            'Magang is forbidden (middleware check)' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_tutup_kas_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('tutup-kas'))
            ->assertStatus($expectedStatus);
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL DETAIL RIWAYAT TUTUP KAS
    // =========================================================================

    public function test_authorized_user_can_view_closing_detail(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $closing = TutupKas::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'tanggal' => now()->toDateString(),
            'waktu_tutup' => now(),
            'modal_awal' => 500000,
            'total_penjualan' => 1250000,
            'total_servis' => 750000,
            'total_kas_keluar' => 100000,
            'kas_sistem' => 2400000,
            'kas_fisik' => 2400000,
            'selisih' => 0,
            'status_selisih' => 'sesuai',
            'catatan' => 'Closing kasir shift malam',
        ]);

        Livewire::test(TutupKasIndex::class)
            ->call('openDetail', $closing->id)
            ->assertSet('modalDetailOpen', true)
            ->assertSee('Closing kasir shift malam');
    }
}

