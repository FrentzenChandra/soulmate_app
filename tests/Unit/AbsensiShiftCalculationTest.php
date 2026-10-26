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

        // Setup default JamKerja global
        JamKerja::create([
            'cabang_id' => null,
            'nama_shift' => ShiftType::Pagi->value,
            'jam_mulai' => '07:00:00',
            'jam_masuk' => '08:00:00',
            'toleransi_menit' => 2,
            'jam_cutoff' => '13:00:00',
            'is_aktif' => true,
        ]);

        JamKerja::create([
            'cabang_id' => null,
            'nama_shift' => ShiftType::Sore->value,
            'jam_mulai' => '13:00:00',
            'jam_masuk' => '14:00:00',
            'toleransi_menit' => 2,
            'jam_cutoff' => '13:00:00',
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
            // Pagi Cases
            '06:50:00 Pagi Terlambat (Strict Window v1.0)' => [
                '06:50:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                10, // 10 menit sebelum 07:00:00
            ],
            '07:00:00 Pagi Tepat Waktu (Buka Shift)' => [
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
            '08:02:59 Pagi Tepat Waktu (Detik Toleransi 2 Menit Opsi A)' => [
                '08:02:59',
                ShiftType::Pagi,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '08:03:00 Pagi Terlambat (Melewati Toleransi)' => [
                '08:03:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                3, // Terhitung 3 menit dari 08:00
            ],
            '08:30:00 Pagi Terlambat' => [
                '08:30:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                30,
            ],
            '12:45:00 Pagi Terlambat (Strict v1.0 Cutoff 13:00)' => [
                '12:45:00',
                ShiftType::Pagi,
                StatusKehadiran::Terlambat,
                285,
            ],

            // Sore Cases
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
            '14:02:59 Sore Tepat Waktu (Detik Toleransi 2 Menit Sore Opsi A)' => [
                '14:02:59',
                ShiftType::Sore,
                StatusKehadiran::TepatWaktu,
                0,
            ],
            '14:03:00 Sore Terlambat (Melewati Toleransi Sore)' => [
                '14:03:00',
                ShiftType::Sore,
                StatusKehadiran::Terlambat,
                3, // Terhitung 3 menit dari 14:00
            ],
            '15:00:00 Sore Terlambat' => [
                '15:00:00',
                ShiftType::Sore,
                StatusKehadiran::Terlambat,
                60,
            ],
        ];
    }
}
