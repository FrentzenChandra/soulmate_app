<?php

namespace Tests\Unit;

use App\Enums\ShiftType;
use App\Enums\StatusKehadiran;
use App\Models\JamKerja;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AbsensiShiftCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected AbsensiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AbsensiService::class);

        // Setup default JamKerja global 24 jam
        JamKerja::create([
            'cabang_id' => null,
            'nama_shift' => ShiftType::Pagi->value,
            'jam_rentang_mulai' => '00:00:00',
            'jam_mulai' => '07:00:00',
            'jam_masuk' => '08:00:00',
            'toleransi_menit' => 2,
            'batas_kritis_menit' => 120,
            'jam_cutoff' => '12:59:59',
            'is_aktif' => true,
        ]);

        JamKerja::create([
            'cabang_id' => null,
            'nama_shift' => ShiftType::Sore->value,
            'jam_rentang_mulai' => '13:00:00',
            'jam_mulai' => '13:00:00',
            'jam_masuk' => '14:00:00',
            'toleransi_menit' => 2,
            'batas_kritis_menit' => 120,
            'jam_cutoff' => '23:59:59',
            'is_aktif' => true,
        ]);
    }

    #[DataProvider('timeMatrixProvider')]
    public function test_time_matrix_shift_and_late_calculation(
        string $jamAbsen,
        ShiftType $expectedShift,
        StatusKehadiran $expectedStatus,
        int $expectedMenitTelat
    ): void {
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $waktu = Carbon::createFromFormat('Y-m-d H:i:s', "{$today} {$jamAbsen}", 'Asia/Jakarta');

        $result = $this->service->evaluasiKehadiran($waktu);

        $this->assertEquals($expectedShift, $result['shift'], "Gagal pada jam {$jamAbsen} (Shift)");
        $this->assertEquals($expectedStatus, $result['status_kehadiran'], "Gagal pada jam {$jamAbsen} (Status)");
        $this->assertEquals($expectedMenitTelat, $result['menit_keterlambatan'], "Gagal pada jam {$jamAbsen} (Menit Telat)");
    }

    public static function timeMatrixProvider(): array
    {
        return [
            // Pagi Cases - Datang Awal (Kecepetan)
            '06:45:00 Pagi Terlalu Awal' => [
                '06:45:00',
                ShiftType::Pagi,
                StatusKehadiran::TerlaluAwal,
                0,
            ],
            '06:50:00 Pagi Terlalu Awal' => [
                '06:50:00',
                ShiftType::Pagi,
                StatusKehadiran::TerlaluAwal,
                0,
            ],

            // Pagi Cases - Tepat Waktu
            '07:00:00 Pagi Tepat Waktu (Buka Toko)' => [
                '07:00:00',
                ShiftType::Pagi,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '07:30:00 Pagi Tepat Waktu (Normal)' => [
                '07:30:00',
                ShiftType::Pagi,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '08:00:00 Pagi Tepat Waktu (Batas Target)' => [
                '08:00:00',
                ShiftType::Pagi,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '08:02:59 Pagi Tepat Waktu (Detik Toleransi 2 Menit)' => [
                '08:02:59',
                ShiftType::Pagi,
                StatusKehadiran::TepatWaktu,
                0,
            ],

            // Pagi Cases - Terlambat Wajar
            '08:03:00 Pagi Terlambat (Melewati Toleransi)' => [
                '08:03:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                3,
            ],
            '08:30:00 Pagi Terlambat' => [
                '08:30:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                30,
            ],

            // Pagi Cases - Terlambat Kritis (> 120 menit)
            '10:05:00 Pagi Terlambat Kritis' => [
                '10:05:00',
                ShiftType::Pagi,
                StatusKehadiran::TerlambatKritis,
                125,
            ],
            '12:45:00 Pagi Terlambat Kritis (Sebelum Cutoff Pagi)' => [
                '12:45:00',
                ShiftType::Pagi,
                StatusKehadiran::TerlambatKritis,
                285,
            ],

            // Sore Cases - Tepat Waktu
            '13:00:00 Sore Tepat Waktu (Buka Shift Sore)' => [
                '13:00:00',
                ShiftType::Sore,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '13:30:00 Sore Tepat Waktu (Normal)' => [
                '13:30:00',
                ShiftType::Sore,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '14:00:00 Sore Tepat Waktu (Batas Target Sore)' => [
                '14:00:00',
                ShiftType::Sore,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '14:02:59 Sore Tepat Waktu (Detik Toleransi 2 Menit Sore)' => [
                '14:02:59',
                ShiftType::Sore,
                StatusKehadiran::TepatWaktu,
                0,
            ],

            // Sore Cases - Terlambat Wajar
            '14:03:00 Sore Terlambat (Melewati Toleransi Sore)' => [
                '14:03:00',
                ShiftType::Sore,
                StatusKehadiran::Terlambat,
                3,
            ],
            '15:00:00 Sore Terlambat' => [
                '15:00:00',
                ShiftType::Sore,
                StatusKehadiran::Terlambat,
                60,
            ],

            // Sore Cases - Terlambat Kritis (> 120 menit)
            '16:30:00 Sore Terlambat Kritis' => [
                '16:30:00',
                ShiftType::Sore,
                StatusKehadiran::TerlambatKritis,
                150,
            ],
        ];
    }
}
