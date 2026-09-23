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
            ->set('nama', 'Printer Epson LX-310')
            ->set('merk_tipe_spek', 'Dot Matrix 9-Pin 80 Kolom')
            ->set('sn', 'SN-EPSON-LX310-001')
            ->set('distributor', 'PT Epson Indonesia')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-EPSON-LX310-001',
            'nama' => 'Printer Epson LX-310',
            'distributor' => 'PT Epson Indonesia',
        ]);

        $snItem = SerialNumber::where('sn', 'SN-EPSON-LX310-001')->first();

        // Edit
        Livewire::test(SerialNumberIndex::class)
            ->call('openEditModal', $snItem->id)
            ->set('distributor', 'PT Epson Authorized Distri')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('serial_numbers', [
            'id' => $snItem->id,
            'distributor' => 'PT Epson Authorized Distri',
        ]);

        // Search
        Livewire::test(SerialNumberIndex::class)
            ->set('search', 'epson 310')
            ->assertSee('SN-EPSON-LX310-001');

        // Quick Add New Customer & Assign
        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->call('openCustomerModal')
            ->set('new_customer_nama', 'Bpk. Ahmad Santoso')
            ->set('new_customer_hp', '081234567899')
            ->call('saveNewCustomer')
            ->assertHasNoErrors()
            ->set('nama', 'Laptop Lenovo ThinkPad')
            ->set('merk_tipe_spek', 'Core i7 16GB 512GB')
            ->set('sn', 'SN-THINKPAD-X1-001')
            ->set('distributor', 'Lenovo ID')
            ->call('save')
            ->assertHasNoErrors();

        $newCustomer = \App\Models\Customer::where('nama', 'Bpk. Ahmad Santoso')->first();
        $this->assertNotNull($newCustomer);

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-THINKPAD-X1-001',
            'customer_id' => $newCustomer->id,
        ]);

        // Search by Customer Name
        Livewire::test(SerialNumberIndex::class)
            ->set('search', 'Ahmad Santoso')
            ->assertSee('SN-THINKPAD-X1-001');
    }
}

