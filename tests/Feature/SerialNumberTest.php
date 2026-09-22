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

        // Quick Add New User & Assign
        Livewire::test(SerialNumberIndex::class)
            ->call('openCreateModal')
            ->call('openUserModal')
            ->set('new_user_name', 'Teknisi Baru Test')
            ->set('new_user_username', 'teknisi.test')
            ->set('new_user_role', 'Teknisi')
            ->call('saveNewUser')
            ->assertHasNoErrors()
            ->set('nama', 'Laptop Lenovo ThinkPad')
            ->set('merk_tipe_spek', 'Core i7 16GB 512GB')
            ->set('sn', 'SN-THINKPAD-X1-001')
            ->set('distributor', 'Lenovo ID')
            ->call('save')
            ->assertHasNoErrors();

        $newUser = User::where('username', 'teknisi.test')->first();
        $this->assertNotNull($newUser);

        $this->assertDatabaseHas('serial_numbers', [
            'sn' => 'SN-THINKPAD-X1-001',
            'user_id' => $newUser->id,
        ]);

        // Search by User Name
        Livewire::test(SerialNumberIndex::class)
            ->set('search', 'Teknisi Baru')
            ->assertSee('SN-THINKPAD-X1-001');
    }
}

