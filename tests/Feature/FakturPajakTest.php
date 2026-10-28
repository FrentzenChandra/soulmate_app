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

    public function test_faktur_pajak_can_be_created_and_deleted(): void
    {
        $component = Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('formNamaPajak', 'RSUD Sehat')
            ->set('formBarang', 'Perangkat Jaringan')
            ->set('formViaPajak', 'E-Katalog')
            ->set('formBruto', '5000000')
            ->call('save')
            ->assertDispatched('swal:toast');

        $faktur = FakturPajak::where('nama_pajak_snapshot', 'RSUD Sehat')->first();
        $this->assertNotNull($faktur);
        $this->assertEquals(5000000, (float) $faktur->bruto);

        // Test delete
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('delete', $faktur->id)
            ->assertDispatched('swal:toast');

        $this->assertNull(FakturPajak::find($faktur->id));
    }

    public function test_open_create_modal_has_empty_form_bruto(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('openCreateModal')
            ->assertSet('formBruto', '');
    }

    // =========================================================================
    // TES TERTARGET: KARTU RINGKASAN METRIK BULAN BERJALAN
    // =========================================================================

    public function test_summary_cards_calculate_totals_only_from_current_month(): void
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        // Faktur 1 di awal bulan berjalan
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-BLN-001',
            'tanggal' => $now->copy()->startOfMonth()->toDateString(),
            'nama_pajak_snapshot' => 'Dinas Pendidikan',
            'barang_snapshot' => 'Laptop Core i5',
            'via_pajak_snapshot' => 'E-Katalog',
            'bruto' => 10000000,
            'dpp' => 8928571.43,
            'ppn_nominal' => 1071428.57,
            'jenis_pph_snapshot' => 'PPh 22',
            'pph_persen' => 1.5,
            'pph_nominal' => 150000,
            'netto' => 8778571.43,
        ]);

        // Faktur 2 di akhir bulan berjalan
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-BLN-002',
            'tanggal' => $now->copy()->endOfMonth()->toDateString(),
            'nama_pajak_snapshot' => 'RSUD Daerah',
            'barang_snapshot' => 'Printer Laser',
            'via_pajak_snapshot' => 'SIPLah',
            'bruto' => 5000000,
            'dpp' => 4464285.71,
            'ppn_nominal' => 535714.29,
            'jenis_pph_snapshot' => 'PPh 23',
            'pph_persen' => 2.0,
            'pph_nominal' => 100000,
            'netto' => 4364285.71,
        ]);

        $namaBulan = $now->locale('id')->translatedFormat('F Y');

        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->assertSeeHtml('Rp 15.000.000')
            ->assertSeeHtml('Rp 1.607.143')
            ->assertSeeHtml('Rp 250.000')
            ->assertSeeHtml('Total Bruto')
            ->assertSeeHtml('Total PPN')
            ->assertSeeHtml('Total PPh')
            ->assertDontSee('Bulan ' . $namaBulan);
    }

    public function test_summary_cards_exclude_last_day_of_last_month_and_first_day_of_next_month(): void
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $lastMonthEnd = $now->copy()->startOfMonth()->subDay()->toDateString();
        $nextMonthStart = $now->copy()->endOfMonth()->addDay()->toDateString();

        // Data hari terakhir bulan lalu (tidak boleh ikut)
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-PREV-MONTH',
            'tanggal' => $lastMonthEnd,
            'nama_pajak_snapshot' => 'Faktur Bulan Lalu',
            'barang_snapshot' => 'Barang Lalu',
            'via_pajak_snapshot' => 'Langsung',
            'bruto' => 20000000,
            'ppn_nominal' => 2400000,
            'pph_nominal' => 300000,
            'jenis_pph_snapshot' => 'Non-PPh',
        ]);

        // Data hari pertama bulan depan (tidak boleh ikut)
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-NEXT-MONTH',
            'tanggal' => $nextMonthStart,
            'nama_pajak_snapshot' => 'Faktur Bulan Depan',
            'barang_snapshot' => 'Barang Depan',
            'via_pajak_snapshot' => 'Langsung',
            'bruto' => 30000000,
            'ppn_nominal' => 3600000,
            'pph_nominal' => 450000,
            'jenis_pph_snapshot' => 'Non-PPh',
        ]);

        // Data sah di dalam bulan berjalan
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-CURRENT-MONTH',
            'tanggal' => $now->copy()->startOfMonth()->addDays(2)->toDateString(),
            'nama_pajak_snapshot' => 'Faktur Sah Bulan Ini',
            'barang_snapshot' => 'Barang Sekarang',
            'via_pajak_snapshot' => 'E-Katalog',
            'bruto' => 7000000,
            'ppn_nominal' => 840000,
            'pph_nominal' => 140000,
            'jenis_pph_snapshot' => 'PPh 23',
        ]);

        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->assertSeeHtml('Rp 7.000.000')
            ->assertSeeHtml('Rp 840.000')
            ->assertSeeHtml('Rp 140.000')
            ->assertDontSeeHtml('Rp 57.000.000')
            ->assertDontSeeHtml('Rp 27.000.000');
    }

    public function test_table_period_filter_does_not_affect_summary_cards(): void
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        // Faktur pada tanggal 1 bulan berjalan
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-LOCK-TEST',
            'tanggal' => $now->copy()->startOfMonth()->toDateString(),
            'nama_pajak_snapshot' => 'Dinas PU',
            'barang_snapshot' => 'Server Mini',
            'via_pajak_snapshot' => 'E-Katalog',
            'bruto' => 12500000,
            'ppn_nominal' => 1500000,
            'pph_nominal' => 250000,
            'jenis_pph_snapshot' => 'PPh 22',
        ]);

        // Ubah mode periode tabel ke 'hari_ini' (jika hari ini bukan tgl 1, filter tabel mengecualikan tgl 1)
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('setPeriodeMode', 'hari_ini')
            ->assertSeeHtml('Rp 12.500.000')
            ->assertSeeHtml('Rp 1.500.000')
            ->assertSeeHtml('Rp 250.000');
    }

    public function test_summary_cards_branch_isolation_for_non_owner_and_owner_active_branch(): void
    {
        $cabang2 = Cabang::create([
            'kode_cabang' => 'SGL',
            'nama_cabang' => 'Sungailiat',
            'alamat' => 'Sungailiat',
            'status' => 'aktif',
        ]);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'pajak.view', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $stafCabang1 = User::create([
            'username' => 'staf_cabang_1',
            'name' => 'Staf Cabang 1',
            'email' => 'staf1@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
        ]);
        $stafCabang1->givePermissionTo('pajak.view');

        $now = \Carbon\Carbon::now('Asia/Jakarta');

        // Faktur Cabang 1
        FakturPajak::create([
            'cabang_id' => $this->cabang->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-CAB-1',
            'tanggal' => $now->copy()->startOfMonth()->toDateString(),
            'nama_pajak_snapshot' => 'Rekanan Cabang 1',
            'barang_snapshot' => 'PC Kasir',
            'via_pajak_snapshot' => 'Langsung',
            'bruto' => 10000000,
            'ppn_nominal' => 1200000,
            'pph_nominal' => 200000,
            'jenis_pph_snapshot' => 'PPh 23',
        ]);

        // Faktur Cabang 2
        FakturPajak::create([
            'cabang_id' => $cabang2->id,
            'user_id' => $this->owner->id,
            'no_faktur' => 'FP-CAB-2',
            'tanggal' => $now->copy()->startOfMonth()->toDateString(),
            'nama_pajak_snapshot' => 'Rekanan Cabang 2',
            'barang_snapshot' => 'PC Server',
            'via_pajak_snapshot' => 'Langsung',
            'bruto' => 50000000,
            'ppn_nominal' => 6000000,
            'pph_nominal' => 1000000,
            'jenis_pph_snapshot' => 'PPh 23',
        ]);

        // Staf Cabang 1 hanya melihat cabangnya sendiri (10jt)
        Livewire::actingAs($stafCabang1)
            ->test(Index::class)
            ->assertSeeHtml('Rp 10.000.000')
            ->assertDontSeeHtml('Rp 50.000.000')
            ->assertDontSeeHtml('Rp 60.000.000');

        // Owner dalam konteks semua cabang (semua) melihat gabungan (60jt)
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->set('cabangFilter', 'semua')
            ->assertSeeHtml('Rp 60.000.000')
            ->assertSeeHtml('Rp 7.200.000')
            ->assertSeeHtml('Rp 1.200.000');

        // Owner memilih filter cabang 2 melihat 50jt
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->set('cabangFilter', (string) $cabang2->id)
            ->assertSeeHtml('Rp 50.000.000')
            ->assertDontSeeHtml('Rp 60.000.000');
    }

    public function test_summary_cards_display_zero_rupiah_when_no_data(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->assertSeeHtml('Rp 0');
    }

    public function test_summary_cards_refresh_automatically_after_new_faktur_created(): void
    {
        $test = Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->assertSeeHtml('Rp 0');

        // Simpan faktur baru di bulan berjalan
        $test->call('openCreateModal')
            ->set('formNamaPajak', 'Kemenag')
            ->set('formBarang', 'Paket Printer')
            ->set('formViaPajak', 'E-Katalog')
            ->set('formBruto', '8000000')
            ->call('save')
            ->assertDispatched('swal:toast');

        // Kartu metrik otomatis menyegar menampilkan angka baru
        $test->assertSeeHtml('Rp 8.000.000');
    }
}
