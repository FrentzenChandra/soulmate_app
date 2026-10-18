<?php

namespace Tests\Feature;

use App\Livewire\Servis\AntrianDisplay;
use App\Models\Cabang;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AntrianDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PermissionMatrixSeeder::class);
    }

    public function test_can_access_antrian_display_as_guest(): void
    {
        $response = $this->get(route('antrian.display'));
        $response->assertOk();
        $response->assertSee('ANTRIAN SERVIS');
        $response->assertSee('LAPTOP');
        $response->assertSee('PRINTER');
        $response->assertSee('KOMPUTER');
        $response->assertSee('PENGERJAAN SERVIS DIAWASI CCTV');
        $response->assertSee('SOLUSI');
    }

    public function test_antrian_component_calculates_correct_counts(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'CB-TEST',
            'nama_cabang' => 'Cabang Test',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['cabang_id' => $cabang->id]);

        $customer = \App\Models\Customer::create([
            'nama' => 'Test Customer',
            'no_hp' => '08123456789',
            'cabang_id' => $cabang->id,
        ]);

        // Buat data servis masuk
        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST-261011-00001',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad',
            'keluhan' => 'Layar bergaris',
            'status' => 'pengecekan',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST-261011-00002',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Asus ROG',
            'keluhan' => 'Sering mati sendiri',
            'status' => 'perbaikan',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST-261011-00003',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'printer',
            'tipe_unit' => 'Epson L3210',
            'keluhan' => 'Hasil cetak putus-putus',
            'status' => 'selesai',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(AntrianDisplay::class, ['cabangId' => $cabang->id])
            ->assertSee('ANTRIAN SERVIS')
            ->assertViewHas('counts', function ($counts) {
                return $counts['laptop']['pengecekan'] === 1
                    && $counts['laptop']['perbaikan'] === 1
                    && $counts['printer']['selesai'] === 1;
            });
    }

    public function test_can_toggle_demo_numbers(): void
    {
        Livewire::test(AntrianDisplay::class)
            ->assertSet('useDemoNumbers', false)
            ->call('toggleDemo')
            ->assertSet('useDemoNumbers', true)
            ->assertViewHas('counts', function ($counts) {
                return $counts['laptop']['pengecekan'] === 3
                    && $counts['laptop']['perbaikan'] === 5
                    && $counts['laptop']['selesai'] === 12
                    && $counts['printer']['pengecekan'] === 1
                    && $counts['printer']['perbaikan'] === 2
                    && $counts['printer']['selesai'] === 7
                    && $counts['komputer']['pengecekan'] === 2
                    && $counts['komputer']['perbaikan'] === 3
                    && $counts['komputer']['selesai'] === 6;
            });
    }
}
