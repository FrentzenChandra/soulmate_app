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
                return $counts['laptop']['pengecekan'] === 332
                    && $counts['laptop']['perbaikan'] === 511
                    && $counts['laptop']['selesai'] === 132
                    && $counts['printer']['pengecekan'] === 142
                    && $counts['printer']['perbaikan'] === 21
                    && $counts['printer']['selesai'] === 723
                    && $counts['komputer']['pengecekan'] === 24
                    && $counts['komputer']['perbaikan'] === 334
                    && $counts['komputer']['selesai'] === 623;
            });
    }

    public function test_antrian_aggregates_menunggu_konfirmasi_and_menunggu_sparepart(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'CB-TEST2',
            'nama_cabang' => 'Cabang Test 2',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['cabang_id' => $cabang->id]);
        $customer = \App\Models\Customer::create([
            'nama' => 'Test Customer 2',
            'no_hp' => '08123456789',
            'cabang_id' => $cabang->id,
        ]);

        // Status menunggu_konfirmasi -> harus masuk ke agregat 'pengecekan'
        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST2-261011-00001',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo Yoga',
            'keluhan' => 'Touchscreen rusak',
            'status' => 'menunggu_konfirmasi',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        // Status menunggu_sparepart -> harus masuk ke agregat 'perbaikan'
        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST2-261011-00002',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'MacBook Air',
            'keluhan' => 'Ganti baterai',
            'status' => 'menunggu_sparepart',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(AntrianDisplay::class, ['cabangId' => $cabang->id])
            ->assertViewHas('counts', function ($counts) {
                return $counts['laptop']['pengecekan'] === 1
                    && $counts['laptop']['perbaikan'] === 1
                    && $counts['laptop']['selesai'] === 0;
            })
            // Klik showList untuk pengecekan -> harus menampilkan Lenovo Yoga
            ->call('showList', 'laptop', 'pengecekan')
            ->assertSet('modalListOpen', true)
            ->assertSee('Lenovo Yoga')
            // Klik showList untuk perbaikan -> harus menampilkan MacBook Air
            ->call('showList', 'laptop', 'perbaikan')
            ->assertSet('modalListOpen', true)
            ->assertSee('MacBook Air');
    }

    public function test_antrian_aggregates_antrean_masuk_into_pengecekan(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => 'CB-TEST3',
            'nama_cabang' => 'Cabang Test 3',
            'status' => 'aktif',
        ]);

        $user = User::factory()->create(['cabang_id' => $cabang->id]);
        $customer = \App\Models\Customer::create([
            'nama' => 'Test Customer 3',
            'no_hp' => '08123456780',
            'cabang_id' => $cabang->id,
        ]);

        // Status antrean_masuk -> harus masuk ke agregat 'pengecekan'
        ServisMasuk::create([
            'no_servis' => 'INV-SV-TEST3-261011-00001',
            'cabang_id' => $cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'kategori_perangkat' => 'komputer',
            'tipe_unit' => 'PC Rakitan Gaming',
            'keluhan' => 'Mati total',
            'status' => 'antrean_masuk',
            'is_real' => true,
            'tanggal_masuk' => now(),
        ]);

        Livewire::test(AntrianDisplay::class, ['cabangId' => $cabang->id])
            ->assertViewHas('counts', function ($counts) {
                return $counts['komputer']['pengecekan'] === 1;
            })
            ->call('showList', 'komputer', 'pengecekan')
            ->assertSet('modalListOpen', true)
            ->assertSee('PC Rakitan Gaming')
            ->assertSee('Antrean Masuk');
    }
}
