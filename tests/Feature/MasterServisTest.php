<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\MasterServis;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterServisTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $teknisi;
    protected User $kasir;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner']);
        Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Teknisi']);
        Role::firstOrCreate(['name' => 'Kasir']);

        $this->cabang = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Soulmate Pangkalpinang',
            'alamat' => 'Jl. Masjid Jamik',
            'no_wa' => '081995466255',
            'status' => 'aktif',
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Owner Test',
            'email' => 'owner@example.com',
            'username' => 'owner_test',
            'cabang_id' => null,
            'status' => 'aktif',
        ]);
        $this->owner->assignRole('Owner');

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'username' => 'admin_test',
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('Admin');

        $this->teknisi = User::factory()->create([
            'name' => 'Teknisi Test',
            'email' => 'teknisi@example.com',
            'username' => 'teknisi_test',
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->teknisi->assignRole('Teknisi');

        $this->kasir = User::factory()->create([
            'name' => 'Kasir Test',
            'email' => 'kasir@example.com',
            'username' => 'kasir_test',
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->kasir->assignRole('Kasir');
    }

    public function test_guests_cannot_access_master_servis(): void
    {
        $response = $this->get(route('master.servis'));
        $response->assertRedirect(route('login'));
    }

    public function test_authorized_roles_can_access_master_servis(): void
    {
        $this->actingAs($this->owner);
        $this->get(route('master.servis'))->assertOk()->assertSee('Master Layanan Servis');

        $this->actingAs($this->admin);
        $this->get(route('master.servis'))->assertOk();

        $this->actingAs($this->teknisi);
        $this->get(route('master.servis'))->assertOk();
    }

    public function test_can_create_jasa_and_sparepart_items(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Jasa Servis
        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('openCreateModal')
            ->set('kode_item', 'JS-999')
            ->set('nama_item', 'Service Keyboard Mechanical')
            ->set('jenis', 'jasa')
            ->set('kategori_perangkat', 'komputer')
            ->set('harga_modal', 10000)
            ->set('harga_jual', 100000)
            ->set('garansi_default_hari', 30)
            ->set('keterangan', 'Perbaikan switch dan solder ulang')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('master_servis', [
            'kode_item' => 'JS-999',
            'nama_item' => 'Service Keyboard Mechanical',
            'jenis' => 'jasa',
            'harga_jual' => 100000,
        ]);

        // 2. Create Sparepart Servis
        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('openCreateModal')
            ->set('kode_item', 'SP-999')
            ->set('nama_item', 'Thermal Paste Arctic MX-6')
            ->set('jenis', 'sparepart')
            ->set('kategori_perangkat', 'semua')
            ->set('harga_modal', 80000)
            ->set('harga_jual', 135000)
            ->set('garansi_default_hari', 0)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('master_servis', [
            'kode_item' => 'SP-999',
            'nama_item' => 'Thermal Paste Arctic MX-6',
            'jenis' => 'sparepart',
            'harga_jual' => 135000,
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('openCreateModal')
            ->set('nama_item', '')
            ->set('harga_jual', '')
            ->call('save')
            ->assertHasErrors(['nama_item', 'harga_jual']);
    }

    public function test_can_edit_existing_item(): void
    {
        $this->actingAs($this->owner);

        $item = MasterServis::create([
            'kode_item' => 'JS-050',
            'nama_item' => 'Deep Cleaning Laptop',
            'jenis' => 'jasa',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 10000,
            'harga_jual' => 80000,
            'garansi_default_hari' => 14,
            'is_aktif' => true,
        ]);

        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('editItem', $item->id)
            ->set('harga_jual', 95000)
            ->set('garansi_default_hari', 30)
            ->call('save')
            ->assertHasNoErrors();

        $item->refresh();
        $this->assertEquals(95000, $item->harga_jual);
        $this->assertEquals(30, $item->garansi_default_hari);
    }

    public function test_delete_or_soft_deactivate_behavior(): void
    {
        $this->actingAs($this->owner);

        // Case 1: Unused item -> Hard deleted
        $unusedItem = MasterServis::create([
            'kode_item' => 'JS-TEMP',
            'nama_item' => 'Layanan Sementara',
            'jenis' => 'jasa',
            'harga_jual' => 50000,
            'is_aktif' => true,
        ]);

        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('confirmDelete', $unusedItem->id)
            ->call('deleteItem')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('master_servis', ['id' => $unusedItem->id]);

        // Case 2: Used item in transactions -> Soft deactivated
        $usedItem = MasterServis::create([
            'kode_item' => 'JS-USED',
            'nama_item' => 'Install Ulang Terpakai',
            'jenis' => 'jasa',
            'harga_jual' => 75000,
            'is_aktif' => true,
        ]);

        $customer = \App\Models\Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Test Pelanggan',
            'no_hp' => '08123456789',
        ]);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB-260918-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $this->owner->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus Vivobook',
            'keluhan' => 'Laptop lemot dan perlu install ulang',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        ServisDetail::create([
            'servis_id' => $servis->id,
            'master_servis_id' => $usedItem->id,
            'nama_item' => $usedItem->nama_item,
            'jenis' => 'jasa',
            'qty' => 1,
            'harga' => 75000,
            'subtotal' => 75000,
        ]);

        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('confirmDelete', $usedItem->id)
            ->call('deleteItem')
            ->assertHasNoErrors();

        $usedItem->refresh();
        $this->assertFalse((bool) $usedItem->is_aktif);
        $this->assertDatabaseHas('master_servis', ['id' => $usedItem->id]);
    }

    public function test_loose_multi_word_search_and_filters(): void
    {
        $this->actingAs($this->owner);

        MasterServis::create(['nama_item' => 'Install Ulang Windows 11 Pro', 'jenis' => 'jasa', 'kategori_perangkat' => 'komputer', 'harga_jual' => 80000, 'is_aktif' => true]);
        MasterServis::create(['nama_item' => 'Ganti Head Printer Canon G2010', 'jenis' => 'jasa', 'kategori_perangkat' => 'printer', 'harga_jual' => 60000, 'is_aktif' => true]);
        MasterServis::create(['nama_item' => 'SSD NVMe Kingston 512GB', 'jenis' => 'sparepart', 'kategori_perangkat' => 'laptop', 'harga_jual' => 500000, 'is_aktif' => true]);

        // Search multi kata: "windows install"
        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->set('search', 'windows install')
            ->assertSee('Install Ulang Windows 11 Pro')
            ->assertDontSee('Ganti Head Printer')
            ->assertDontSee('SSD NVMe Kingston');

        // Filter Jenis: sparepart
        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->set('jenisFilter', 'sparepart')
            ->assertSee('SSD NVMe Kingston')
            ->assertDontSee('Install Ulang Windows 11 Pro');
    }

    public function test_sparepart_stock_creation_and_editing(): void
    {
        $this->actingAs($this->owner);

        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('openCreateModal')
            ->set('kode_item', 'SP-RAM-01')
            ->set('nama_item', 'RAM DDR4 8GB 3200MHz')
            ->set('jenis', 'sparepart')
            ->set('kategori_perangkat', 'laptop')
            ->set('harga_modal', 250000)
            ->set('harga_jual', 350000)
            ->set("initialStock.{$this->cabang->id}", 5)
            ->set("minStock.{$this->cabang->id}", 2)
            ->call('save')
            ->assertHasNoErrors();

        $item = MasterServis::where('kode_item', 'SP-RAM-01')->first();
        $this->assertNotNull($item);
        $this->assertDatabaseHas('master_servis_stoks', [
            'master_servis_id' => $item->id,
            'cabang_id' => $this->cabang->id,
            'jumlah' => 5,
            'min_stok' => 2,
        ]);

        // Edit stock
        Livewire::test(\App\Livewire\Master\Servis\Index::class)
            ->call('editItem', $item->id)
            ->set("initialStock.{$this->cabang->id}", 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('master_servis_stoks', [
            'master_servis_id' => $item->id,
            'cabang_id' => $this->cabang->id,
            'jumlah' => 1,
            'min_stok' => 2,
        ]);
    }

    public function test_notification_bell_triggers_for_critical_sparepart(): void
    {
        $this->actingAs($this->owner);

        $item = MasterServis::create([
            'kode_item' => 'SP-CRIT',
            'nama_item' => 'Keyboard Lenovo Ideapad 320',
            'jenis' => 'sparepart',
            'kategori_perangkat' => 'laptop',
            'harga_modal' => 90000,
            'harga_jual' => 150000,
            'is_aktif' => true,
        ]);

        \App\Models\MasterServisStok::create([
            'master_servis_id' => $item->id,
            'cabang_id' => $this->cabang->id,
            'jumlah' => 1,
            'min_stok' => 3,
            'is_aktif' => true,
        ]);

        Livewire::test(\App\Livewire\Components\NotificationBell::class)
            ->assertSee('Keyboard Lenovo Ideapad 320')
            ->assertSee('Sparepart')
            ->assertSee('Sisa 1');
    }
}
