<?php

namespace Tests\Feature;

use App\Livewire\FakturPajak\Index;
use App\Models\Cabang;
use App\Models\FakturPajak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FakturPajakTest extends TestCase
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
            ['username' => 'owner_test_pajak'],
            [
                'name' => 'Owner Test Pajak',
                'email' => 'owner_test_pajak@soulmatekomputer.com',
                'password' => bcrypt('password'),
                'cabang_id' => $this->cabang->id,
            ]
        );

        if (!$this->owner->hasRole('Owner')) {
            $this->owner->assignRole('Owner');
        }
    }

    public function test_faktur_pajak_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->owner)->get(route('faktur-pajak.index'));
        $response->assertStatus(200);
        $response->assertSee('Faktur Pajak');
        $response->assertSee('Konteks HQ (Semua Cabang)');
    }

    public function test_faktur_pajak_livewire_component_renders_and_toggles_selesai(): void
    {
        $faktur = FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-TEST-001',
            'tanggal' => now()->toDateString(),
            'nama_pajak_snapshot' => 'Dinas Pendidikan',
            'barang_snapshot' => 'Pengadaan Laptop',
            'via_pajak_snapshot' => 'E-Katalog',
            'kode_pajak_snapshot' => '020',
            'bruto' => 10000000,
            'dpp' => 9009009,
            'dpp_lain' => 8258258,
            'ppn_nominal' => 990991,
            'jenis_pph_snapshot' => 'PPh 22 (1.5%)',
            'pph_persen' => 1.5,
            'pph_nominal' => 123874,
            'netto' => 8885135,
            'is_selesai' => false,
        ]);

        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->assertStatus(200)
            ->call('toggleSelesai', $faktur->id);

        $this->assertTrue($faktur->fresh()->is_selesai);
    }
}
