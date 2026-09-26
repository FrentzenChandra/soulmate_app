<?php

namespace Tests\Feature;

use App\Livewire\SerialNumber\Index as SerialNumberIndex;
use App\Models\SerialNumber;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SerialNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_serial_number()
    {
        $this->get(route('serial-number'))->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_access_and_crud_serial_number()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $kasir = User::where('email', 'kasir.pkp@soulmate.test')->first();
        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $this->actingAs($kasir);
        $this->get(route('serial-number'))->assertStatus(200);

        $this->actingAs($teknisi);
        $this->get(route('serial-number'))->assertStatus(403);

        $this->actingAs($admin);
        $this->get(route('serial-number'))->assertStatus(200);

        // Create
        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->set('merk', 'Asus')
            ->set('tipe_spek', 'ROG Strix G15 G513')
            ->set('sn', 'SN-ASUS-ROG-001')
            ->set('distributor', 'PT Asus Indonesia')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-ASUS-ROG-001',
            'merk' => 'Asus',
            'tipe_spek' => 'ROG Strix G15 G513',
            'distributor' => 'PT Asus Indonesia',
        ]);

        $snItem = SerialNumber::where('sn', 'SN-ASUS-ROG-001')->first();

        // Edit
        Livewire::test(SerialNumberIndex::class)
            ->call('openEditModal', $snItem->id)
            ->set('distributor', 'PT Asus Authorized Distri')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'id' => $snItem->id,
            'distributor' => 'PT Asus Authorized Distri',
        ]);

        // Search
        Livewire::test(SerialNumberIndex::class)
            ->set('search', 'asus rog')
            ->assertSee('SN-ASUS-ROG-001');

        // Add New Customer via customerType = 'baru' & Custom Brand Create
        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->set('customerType', 'baru')
            ->set('newCustomerNama', 'Bpk. Ahmad Santoso')
            ->set('newCustomerNoHp', '081234567899')
            ->set('merk', '__custom__')
            ->set('custom_merk', 'Lenovo Custom')
            ->set('tipe_spek', 'ThinkPad X1 Carbon Gen 10')
            ->set('sn', 'SN-THINKPAD-X1-001')
            ->set('distributor', 'Lenovo ID')
            ->call('save')
            ->assertHasNoErrors();

        $newCustomer = \App\Models\Customer::where('nama', 'Bpk. Ahmad Santoso')->first();
        $this->assertNotNull($newCustomer);

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-THINKPAD-X1-001',
            'customer_id' => $newCustomer->id,
            'merk' => 'Lenovo Custom',
            'tipe_spek' => 'ThinkPad X1 Carbon Gen 10',
        ]);

        // Test Selecting Existing Customer via Autocomplete
        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->set('customerType', 'langganan')
            ->set('customerSearch', 'Ahmad')
            ->call('selectCustomer', $newCustomer->id, $newCustomer->nama)
            ->set('merk', 'Acer')
            ->set('tipe_spek', 'Nitro 5 AN515')
            ->set('sn', 'SN-ACER-NITRO-001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-ACER-NITRO-001',
            'customer_id' => $newCustomer->id,
            'merk' => 'Acer',
        ]);

        // Search by Customer Name & Single Character & Test Periode Mode Filters
        Livewire::test(SerialNumberIndex::class)
            ->set('search', 'Ahmad Santoso')
            ->assertSee('SN-THINKPAD-X1-001')
            ->set('search', 'r')
            ->assertSee('Total Unit per Brand Laptop')
            ->assertSee('Asus')
            ->assertSee('Lenovo Custom')
            ->call('setPeriodeMode', 'semua')
            ->assertSee('Unit Total')
            ->call('setPeriodeMode', 'hari_ini')
            ->assertSee('Unit Total')
            ->call('setPeriodeMode', 'range')
            ->assertSee('Unit Total')
            ->call('setPeriodeMode', 'bulanan')
            ->set('filterBulan', date('m'))
            ->assertSee('Unit Total')
            ->call('setPeriodeMode', 'tahunan')
            ->set('filterTahun', date('Y'))
            ->assertSee('Unit Total');
    }
}

