<?php

namespace Tests\Feature;

use App\Livewire\Master\OrangPajak\Index;
use App\Models\Cabang;
use App\Models\MasterOrangPajak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterOrangPajakTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang = Cabang::firstOrCreate(
            ['kode_cabang' => 'TGR'],
            ['nama_cabang' => 'Tangerang', 'alamat' => 'Tangerang', 'status' => 'aktif']
        );

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $this->owner = User::firstOrCreate(
            ['username' => 'owner_orang_pajak_test'],
            [
                'name' => 'Owner Test',
                'email' => 'owner_orang_pajak@soulmatekomputer.com',
                'password' => bcrypt('password'),
                'cabang_id' => $this->cabang->id,
            ]
        );

        if (!$this->owner->hasRole('Owner')) {
            $this->owner->assignRole('Owner');
        }
    }

    public function test_orang_pajak_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->owner)->get(route('master.orang-pajak'));
        $response->assertStatus(200);
        $response->assertSee('Master Orang Pajak');
    }

    public function test_orang_pajak_can_be_created_and_updated_and_deleted(): void
    {
        // 1. Create
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('nama', 'Dinas Kesehatan Tangerang')
            ->set('npwp', '01.234.567.8-901.000')
            ->set('kontak', '081234567890')
            ->set('is_aktif', true)
            ->call('save')
            ->assertDispatched('swal:toast');

        $orang = MasterOrangPajak::where('nama', 'Dinas Kesehatan Tangerang')->first();
        $this->assertNotNull($orang);
        $this->assertEquals('01.234.567.8-901.000', $orang->npwp);

        // 2. Edit
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('edit', $orang->id)
            ->set('nama', 'Dinas Kesehatan Tangerang Updated')
            ->call('save')
            ->assertDispatched('swal:toast');

        $this->assertEquals('Dinas Kesehatan Tangerang Updated', $orang->fresh()->nama);

        // 3. Delete
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('delete', $orang->id)
            ->assertDispatched('swal:toast');

        $this->assertNull(MasterOrangPajak::find($orang->id));
    }

    public function test_validation_fails_when_nama_is_empty(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('nama', '')
            ->call('save')
            ->assertHasErrors(['nama' => 'required']);
    }
}

