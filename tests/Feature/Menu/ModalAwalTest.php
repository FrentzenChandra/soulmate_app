<?php

namespace Tests\Feature\Menu;

use App\Livewire\ModalAwal\Index as ModalAwalIndex;
use App\Models\Cabang;
use App\Models\ModalAwal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class ModalAwalTest extends TestCase
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
    // TAHAP 2A: AKSES & OTORISASI PER ROLE (RULE 12)
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('modal-awal'))
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
    public function test_role_access_to_modal_awal_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('modal-awal'))
            ->assertStatus($expectedStatus);
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL INPUT & EDIT MODAL AWAL (RULE 12 COMPLIANCE)
    // =========================================================================

    public function test_admin_can_record_modal_awal_for_today(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(ModalAwalIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nominal', 500000)
            ->set('keterangan', 'Modal uang kembalian pecahan kecil')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('modal_awals', [
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'nominal' => 500000,
            'keterangan' => 'Modal uang kembalian pecahan kecil',
        ]);
    }

    public function test_admin_blocked_from_double_input_on_the_same_day(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        ModalAwal::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 500000,
        ]);

        // Percobaan buka modal input kedua kali oleh Admin
        Livewire::test(ModalAwalIndex::class)
            ->call('openCreateModal')
            ->assertDispatched('swal:error');
    }

    public function test_superadmin_can_edit_existing_modal_awal(): void
    {
        $superadmin = $this->actingAsRole('Superadmin', [], $this->cabang1);

        $modal = ModalAwal::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $superadmin->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 300000,
        ]);

        Livewire::test(ModalAwalIndex::class)
            ->call('openEditModal', $modal->id)
            ->assertSet('modalOpen', true)
            ->set('nominal', 600000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(600000, $modal->fresh()->nominal);
    }
}

