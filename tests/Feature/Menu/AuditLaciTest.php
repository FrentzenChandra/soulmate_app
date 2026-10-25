<?php

namespace Tests\Feature\Menu;

use App\Livewire\AuditLaci\Index as AuditLaciIndex;
use App\Models\Cabang;
use App\Models\CekKasLog;
use App\Models\ModalAwal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class AuditLaciTest extends TestCase
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
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (EKSKLUSIF OWNER)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('audit-laci'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Owner has access' => ['Owner', 200],
            'Superadmin is forbidden' => ['Superadmin', 403],
            'Admin is forbidden' => ['Admin', 403],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Teknisi is forbidden' => ['Teknisi', 403],
            'Operasional is forbidden' => ['Operasional', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_audit_laci_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('audit-laci'))
            ->assertStatus($expectedStatus);
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL PROSES AUDIT LACI KASIR (SNAPSHOT)
    // =========================================================================

    public function test_owner_can_process_audit_laci(): void
    {
        $owner = $this->actingAsRole('Owner');

        ModalAwal::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $owner->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 500000,
        ]);

        Livewire::test(AuditLaciIndex::class)
            ->call('openAuditModal')
            ->assertSet('modalAuditOpen', true)
            ->set('kasFisik', 500000)
            ->set('catatanAudit', 'Kas awal shift pagi sesuai')
            ->call('prosesAudit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cek_kas_logs', [
            'cabang_id' => $this->cabang1->id,
            'user_id' => $owner->id,
            'modal_awal' => 500000,
            'kas_fisik' => 500000,
            'status_selisih' => 'klop',
        ]);
    }
}

