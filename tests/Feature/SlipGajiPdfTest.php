<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\SlipGaji;
use App\Models\SlipGajiItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlipGajiPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_export_slip_gaji_pdf(): void
    {
        $slip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/001',
            'cabang_id' => 1,
            'user_id' => 1,
            'nama_staf_snapshot' => 'Karyawan Test',
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2500000,
            'total_bruto' => 2500000,
            'total_pengurangan' => 0,
            'total_diterima' => 2500000,
            'created_by' => 1,
        ]);

        $this->get(route('slip-gaji.pdf', $slip->id))
            ->assertRedirect(route('login'));
    }

    public function test_cannot_export_draft_slip_as_pdf(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $draftSlip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'nama_staf_snapshot' => $staf->name,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'draft',
            'gaji_pokok' => 2500000,
            'total_bruto' => 2500000,
            'total_pengurangan' => 0,
            'total_diterima' => 2500000,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner);
        $this->get(route('slip-gaji.pdf', $draftSlip->id))
            ->assertStatus(400);
    }

    public function test_non_owner_cannot_export_slip_from_other_branch(): void
    {
        // Admin PKP (cabang 1)
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();

        // Slip Final cabang 2 (Belinyu)
        $otherBranchSlip = SlipGaji::create([
            'no_slip' => 'SLIP/BLY/2026/10/001',
            'cabang_id' => 2,
            'user_id' => 1,
            'nama_staf_snapshot' => 'Karyawan Belinyu',
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'gaji_pokok' => 2500000,
            'total_bruto' => 2500000,
            'total_pengurangan' => 0,
            'total_diterima' => 2500000,
            'created_by' => 1,
        ]);

        $this->actingAs($admin);
        $this->get(route('slip-gaji.pdf', $otherBranchSlip->id))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_export_final_slip_pdf_with_option_a_total_diterima(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $finalSlip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/001',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'nama_staf_snapshot' => $staf->name,
            'bagian_snapshot' => 'Teknisi Printer',
            'gaji_pokok' => 2500000,
            'uang_makan_hari' => 26,
            'uang_makan_per_hari' => 20000,
            'uang_makan_total' => 520000,
            'uang_transport_hari' => 26,
            'uang_transport_per_hari' => 20000,
            'uang_transport_total' => 520000,
            'tunjangan_nama' => 'Tunjangan Khusus',
            'tunjangan_nominal' => 800000,
            'bonus_nominal' => 0, // Rp 0 -> tidak boleh muncul
            'lembur_performa_nominal' => 0, // Rp 0 -> tidak boleh muncul
            'servisan_selesai_nominal' => 0, // Rp 0 -> tidak boleh muncul
            'total_bruto' => 4340000,
            'total_pengurangan' => 100000,
            'total_diterima' => 4240000,
            'created_by' => $owner->id,
            'finalized_by' => $owner->id,
            'finalized_at' => now(),
        ]);

        SlipGajiItem::create([
            'slip_gaji_id' => $finalSlip->id,
            'tipe' => 'pengurangan',
            'kategori' => 'Kasbon',
            'nama_item' => 'Kasbon',
            'nominal' => 100000,
            'urutan' => 1,
        ]);

        $this->actingAs($owner);

        $response = $this->get(route('slip-gaji.pdf', $finalSlip->id));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        // Test view template contents directly
        $view = $this->view('pdf.slip-gaji', ['slip' => $finalSlip]);

        // Verifikasi Label Opsi A "Total Diterima"
        $view->assertSee('TOTAL DITERIMA', false);
        $view->assertSee('4.240.000', false);

        // Verifikasi nama staf & format hari berspasi
        $view->assertSee($staf->name, false);
        $view->assertSee('26 Hari', false);

        // Verifikasi komponen Rp 0 disembunyikan
        $view->assertDontSee('Lembur/Performa');
        $view->assertDontSee('Servisan Selesai');
    }

    public function test_authorized_user_can_access_cetak_view_with_option_2(): void
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $staf = User::where('email', 'teknisi.pkp@soulmate.test')->first();

        $finalSlip = SlipGaji::create([
            'no_slip' => 'SLIP/PKP/2026/10/002',
            'cabang_id' => $staf->cabang_id,
            'user_id' => $staf->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'final',
            'nama_staf_snapshot' => $staf->name,
            'bagian_snapshot' => 'Teknisi Laptop',
            'gaji_pokok' => 2500000,
            'total_bruto' => 2500000,
            'total_pengurangan' => 0,
            'total_diterima' => 2500000,
            'created_by' => $owner->id,
            'finalized_by' => $owner->id,
            'finalized_at' => now(),
        ]);

        $this->actingAs($owner);

        $response = $this->get(route('slip-gaji.cetak', $finalSlip->id));
        $response->assertOk();
        $response->assertViewIs('pdf.slip-gaji-print');
        $response->assertSee('TOTAL DITERIMA KARYAWAN', false);
        $response->assertSee('window.print()', false);
    }
}

