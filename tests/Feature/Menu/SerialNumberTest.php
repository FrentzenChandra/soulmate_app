<?php

namespace Tests\Feature\Menu;

use App\Livewire\SerialNumber\Index as SerialNumberIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\SerialNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class SerialNumberTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Customer $customer;

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

        $this->customer = Customer::create([
            'cabang_id' => $this->cabang1->id,
            'nama' => 'Dwi Cahyo',
            'no_hp' => '081277665544',
        ]);
    }

    // =========================================================================
    // TAHAP 2A: AKSES & OTORISASI PER ROLE
    // =========================================================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('serial-number'))
            ->assertRedirect(route('login'));
    }

    public static function roleAccessProvider(): array
    {
        return [
            'Superadmin has access' => ['Superadmin', 200],
            'Admin has access' => ['Admin', 200],
            'Teknisi has access' => ['Teknisi', 200],
            'Operasional has access' => ['Operasional', 200],
            'Owner has access via bypass' => ['Owner', 200],
            'Bendahara is forbidden' => ['Bendahara', 403],
            'Magang is forbidden' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_role_access_to_serial_number_page(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role, [], $this->cabang1);

        $this->get(route('serial-number'))
            ->assertStatus($expectedStatus);
    }

    public function test_user_with_direct_permission_can_access(): void
    {
        $this->actingAsUserWithPermission('serial_number.view', [], $this->cabang1);

        $this->get(route('serial-number'))
            ->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->actingAsUserWithPermission('absensi.view', [], $this->cabang1);

        $this->get(route('serial-number'))
            ->assertForbidden();
    }

    // =========================================================================
    // TAHAP 2B: FUNGSIONAL CRUD SERIAL NUMBER
    // =========================================================================

    public function test_authorized_user_can_create_serial_number(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('customerType', 'langganan')
            ->set('selectedCustomerId', $this->customer->id)
            ->set('customerSearch', $this->customer->nama)
            ->set('merk', 'Asus')
            ->set('tipe_spek', 'ROG Zephyrus G14 GA402RJ')
            ->set('sn', 'SN-ASUS-9988221100')
            ->set('distributor', 'PT Synnex Metrodata')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'cabang_id' => $this->cabang1->id,
            'customer_id' => $this->customer->id,
            'merk' => 'Asus',
            'sn' => 'SN-ASUS-9988221100',
            'distributor' => 'PT Synnex Metrodata',
        ]);
    }

    public function test_authorized_user_can_edit_serial_number(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $sn = SerialNumber::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'customer_id' => $this->customer->id,
            'nama' => 'Lenovo Legion 5',
            'merk' => 'Lenovo',
            'tipe_spek' => 'Legion 5 15ACH6H',
            'merk_tipe_spek' => 'Lenovo Legion 5 15ACH6H',
            'sn' => 'PF3ABCDE',
            'distributor' => 'PT Astrindo',
        ]);

        Livewire::test(SerialNumberIndex::class)
            ->call('openEditModal', $sn->id)
            ->assertSet('modalOpen', true)
            ->set('tipe_spek', 'Legion 5 Pro 16ACH6H')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('Legion 5 Pro 16ACH6H', $sn->fresh()->tipe_spek);
    }

    public function test_authorized_user_can_delete_serial_number(): void
    {
        $admin = $this->actingAsRole('Admin', [], $this->cabang1);

        $sn = SerialNumber::create([
            'cabang_id' => $this->cabang1->id,
            'user_id' => $admin->id,
            'customer_id' => $this->customer->id,
            'nama' => 'Acer Swift 3',
            'merk' => 'Acer',
            'tipe_spek' => 'SF314-511',
            'merk_tipe_spek' => 'Acer Swift 3 SF314-511',
            'sn' => 'NXABFSG001',
        ]);

        Livewire::test(SerialNumberIndex::class)
            ->call('delete', $sn->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('serial_numbers', [
            'id' => $sn->id,
        ]);
    }

    // =========================================================================
    // TAHAP 3: FRONTEND ASSERTIONS & SIDEBAR
    // =========================================================================

    public function test_sidebar_displays_serial_number_for_authorized_roles(): void
    {
        $this->actingAsRole('Admin', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('serial-number'));

        $this->actingAsRole('Teknisi', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('serial-number'));
    }

    public function test_sidebar_does_not_display_serial_number_for_unauthorized_roles(): void
    {
        $this->actingAsRole('Bendahara', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('serial-number'));

        $this->actingAsRole('Magang', [], $this->cabang1);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('serial-number'));
    }
}

