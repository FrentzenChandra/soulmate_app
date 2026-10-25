<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Customer\Index as MasterCustomerIndex;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\ServisMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterCustomerTest extends TestCase
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
    public function test_akses_halaman_master_customer_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $response = $this->get(route('master.customer'));

        $response->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $response = $this->get(route('master.customer'));

        $response->assertRedirect(route('login'));
    }

    public function test_role_tanpa_permission_view_ditolak_403(): void
    {
        $this->setupRolesAndPermissions();
        $customRole = Role::firstOrCreate(['name' => 'TamuKhusus', 'guard_name' => 'web']);
        $customRole->syncPermissions([]); // Tanpa permission

        $this->actingAsRole('TamuKhusus');

        $response = $this->get(route('master.customer'));

        $response->assertStatus(403);
    }

    public function test_render_komponen_livewire_master_customer(): void
    {
        $admin = $this->actingAsRole('Admin');

        Customer::create([
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Hendra Setiawan',
            'no_hp' => '081234567890',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->assertStatus(200)
            ->assertSee('Hendra Setiawan')
            ->assertSee('081234567890');
    }

    public function test_tambah_customer_berhasil(): void
    {
        $admin = $this->actingAsRole('Admin');

        Livewire::test(MasterCustomerIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', false)
            ->set('nama', 'Dewi Lestari')
            ->set('no_hp', '085678901234')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('customers', [
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Dewi Lestari',
            'no_hp' => '085678901234',
        ]);
    }

    public function test_validasi_nama_customer_wajib_diisi(): void
    {
        $this->actingAsRole('Admin');

        Livewire::test(MasterCustomerIndex::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->call('save')
            ->assertHasErrors(['nama' => 'required']);
    }

    public function test_edit_customer_berhasil_oleh_admin(): void
    {
        $admin = $this->actingAsRole('Admin');

        $customer = Customer::create([
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Bambang Pamungkas',
            'no_hp' => '0811112222',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->call('openEditModal', $customer->id)
            ->assertSet('modalOpen', true)
            ->assertSet('isEditMode', true)
            ->assertSet('nama', 'Bambang Pamungkas')
            ->set('nama', 'Bambang Pamungkas Update')
            ->set('no_hp', '0899998888')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'nama' => 'Bambang Pamungkas Update',
            'no_hp' => '0899998888',
        ]);
    }

    public function test_proteksi_edit_customer_cabang_lain_ditolak(): void
    {
        $adminCabang1 = $this->actingAsRole('Admin');

        $cabang2 = Cabang::create([
            'kode_cabang' => 'CB-2',
            'nama_cabang' => 'Cabang Dua',
            'alamat' => 'Jl. Lain',
            'no_wa' => '0812345678',
            'status' => 'aktif',
        ]);

        $customerCabang2 = Customer::create([
            'cabang_id' => $cabang2->id,
            'nama' => 'Pelanggan Cabang Lain',
            'no_hp' => '087777777',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->call('openEditModal', $customerCabang2->id)
            ->assertSet('modalOpen', false);
    }

    public function test_admin_tanpa_permission_delete_ditolak_403_saat_hapus(): void
    {
        $admin = $this->actingAsRole('Admin');

        $customer = Customer::create([
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Customer Test Delete',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->call('deleteCustomer', $customer->id)
            ->assertForbidden();
    }

    public function test_superadmin_dapat_hapus_customer_tanpa_relasi(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $customer = Customer::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Customer Akan Dihapus',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->call('deleteCustomer', $customer->id);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_dengan_riwayat_servis_tidak_bisa_dihapus(): void
    {
        $superadmin = $this->actingAsRole('Superadmin');

        $customer = Customer::create([
            'cabang_id' => $superadmin->cabang_id,
            'nama' => 'Customer Punya Servis',
            'tipe' => 'langganan',
        ]);

        ServisMasuk::create([
            'cabang_id' => $superadmin->cabang_id,
            'customer_id' => $customer->id,
            'created_by' => $superadmin->id,
            'no_servis' => 'INV-SV-TEST-0001',
            'tanggal_masuk' => now()->toDateString(),
            'tipe_layanan' => 'reguler',
            'status' => 'pengecekan',
            'tipe_perangkat' => 'Laptop',
            'nama_unit' => 'Lenovo ThinkPad',
            'keluhan' => 'Layar Blank',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->call('deleteCustomer', $customer->id);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_pencarian_multi_kata_customer(): void
    {
        $admin = $this->actingAsRole('Admin');

        Customer::create([
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Muhammad Rizky Pratama',
            'no_hp' => '0812349999',
            'tipe' => 'langganan',
        ]);

        Customer::create([
            'cabang_id' => $admin->cabang_id,
            'nama' => 'Siti Aisyah Putri',
            'no_hp' => '0898761111',
            'tipe' => 'langganan',
        ]);

        Livewire::test(MasterCustomerIndex::class)
            ->set('search', 'Rizky Pratama')
            ->assertSee('Muhammad Rizky Pratama')
            ->assertDontSee('Siti Aisyah Putri');
    }
}
