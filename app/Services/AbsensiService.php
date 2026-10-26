<?php

namespace App\Services;

use App\Enums\ShiftType;
use App\Enums\StatusKehadiran;
use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\User;
use App\Models\WifiAbsen;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;

class AbsensiService
{
    /**
     * Dapatkan key cache untuk daftar IP aktif per cabang.
     */
    public static function getCacheKey(int $cabangId): string
    {
        return "wifi_absen_ips_cabang_{$cabangId}";
    }

    /**
     * Bersihkan cache IP aktif per cabang.
     */
    public static function clearCache(int $cabangId): void
    {
        Cache::forget(self::getCacheKey($cabangId));
    }

    /**
     * Cek status absensi user untuk hari ini (Asia/Jakarta).
     */
    public function statusHariIni(User $user): ?Absensi
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        return Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();
    }

    /**
     * Cocokkan apakah IP client sesuai dengan IP atau pola subnet yang didaftarkan.
     * Mendukung:
     * - Exact match (contoh: 127.0.0.1, 36.85.10.15)
     * - IPv4 Wildcard Subnet (contoh: 192.168.18.*)
     * - IPv6 Wildcard Subnet (contoh: 2404:8000:1036:ac2:*)
     * - Smart IPv6 /64 router prefix matching
     */
    public static function isIpMatch(string $clientIp, string $registeredPattern): bool
    {
        $client = strtolower(trim($clientIp));
        $pattern = strtolower(trim($registeredPattern));

        if ($client === '' || $pattern === '') {
            return false;
        }

        // 1. Exact match
        if ($client === $pattern) {
            return true;
        }

        // 2. Wildcard pattern matching (contoh: 192.168.18.* atau 2404:8000:1036:ac2:*)
        if (str_contains($pattern, '*')) {
            if (fnmatch($pattern, $client)) {
                return true;
            }
        }

        // 3. Prefix match jika diakhiri titik atau titik dua
        if (str_ends_with($pattern, '.') || str_ends_with($pattern, ':')) {
            if (str_starts_with($client, $pattern)) {
                return true;
            }
        }

        // 4. Smart IPv6 Subnet /64 matching jika kedua alamat merupakan IPv6
        if (str_contains($client, ':') && str_contains($pattern, ':')) {
            $cleanPattern = rtrim(str_replace('*', '', $pattern), ':');
            if ($cleanPattern !== '' && str_starts_with($client, $cleanPattern)) {
                return true;
            }

            // Normalisasi 4 hextet pertama (/64 router subnet)
            $clientHextets = explode(':', $client);
            $patternHextets = explode(':', $pattern);
            if (count($clientHextets) >= 4 && count($patternHextets) >= 4) {
                $clientPrefix = implode(':', array_slice($clientHextets, 0, 4));
                $patternPrefix = implode(':', array_slice($patternHextets, 0, 4));
                if ($clientPrefix === $patternPrefix) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validasi apakah IP client terdaftar pada WiFi aktif cabang user.
     * Menggunakan cache 5 menit (300 detik) per cabang.
     */
    public function jaringanValid(User $user, ?string $ip = null): bool
    {
        if (! $user->cabang_id) {
            return false;
        }

        $clientIp = trim($ip ?? request()->ip() ?? '');
        if ($clientIp === '') {
            return false;
        }

        $activeIps = Cache::remember(self::getCacheKey($user->cabang_id), 300, function () use ($user) {
            return WifiAbsen::where('cabang_id', $user->cabang_id)
                ->where('is_aktif', true)
                ->pluck('ip_address')
                ->map(fn ($val) => trim($val))
                ->toArray();
        });

        foreach ($activeIps as $registeredIp) {
            if (self::isIpMatch($clientIp, $registeredIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluasi klasifikasi shift dan status keterlambatan absensi (Strict Window v1.0 + Toleransi 2 Menit Opsi A).
     */
    public function evaluasiKehadiran(Carbon $waktu, ?int $cabangId = null): array
    {
        $jamStr = $waktu->format('H:i:s');

        // 1. Tentukan Shift berdasarkan Cutoff
        $rulePagi = JamKerja::getRuleFor(ShiftType::Pagi, $cabangId);
        $cutoff = $rulePagi?->jam_cutoff ?? '13:00:00';

        if ($jamStr < $cutoff) {
            $shift = ShiftType::Pagi;
            $rule = $rulePagi;
            $defaultMulai = '07:00:00';
            $defaultMasuk = '08:00:00';
        } else {
            $shift = ShiftType::Sore;
            $rule = JamKerja::getRuleFor(ShiftType::Sore, $cabangId);
            $defaultMulai = '13:00:00';
            $defaultMasuk = '14:00:00';
        }

        $jamMulai = $rule?->jam_mulai ?? $defaultMulai;
        $jamMasuk = $rule?->jam_masuk ?? $defaultMasuk;
        $toleransiMenit = (int) ($rule?->toleransi_menit ?? 2);

        // Batas waktu toleransi (contoh: 08:00:00 + 2 menit = 08:02:59)
        $targetCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $waktu->toDateString() . ' ' . $jamMasuk, 'Asia/Jakarta');
        $toleransiCarbon = $targetCarbon->copy()->addMinutes($toleransiMenit)->endOfMinute(); // :59 detik

        $mulaiCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $waktu->toDateString() . ' ' . $jamMulai, 'Asia/Jakarta');

        if ($waktu->lt($mulaiCarbon)) {
            // Strict Window: Masuk sebelum jam_mulai dianggap Terlambat (di luar window resmi)
            $status = StatusKehadiran::Terlambat;
            $menitTelat = (int) $waktu->diffInMinutes($mulaiCarbon, false);
            if ($menitTelat < 0) {
                $menitTelat = abs($menitTelat);
            }
        } elseif ($waktu->lte($toleransiCarbon)) {
            // Masuk dalam window tepat waktu (jam_mulai s/d jam_masuk + toleransi)
            $status = StatusKehadiran::TepatWaktu;
            $menitTelat = 0;
        } else {
            // Melewati toleransi (misal >= 08:03:00 atau >= 14:03:00)
            $status = StatusKehadiran::Terlambat;
            $menitTelat = (int) $targetCarbon->diffInMinutes($waktu, false);
            if ($menitTelat < 0) {
                $menitTelat = 0;
            }
        }

        return [
            'shift' => $shift,
            'status_kehadiran' => $status,
            'menit_keterlambatan' => $menitTelat,
            'snapshot_jam_masuk' => $jamMasuk,
            'snapshot_toleransi_menit' => $toleransiMenit,
        ];
    }

    /**
     * Lakukan pencatatan absensi masuk untuk user.
     * Waktu diset ke Asia/Jakarta. Validasi IP dilakukan langsung di dalam service.
     * Memvalidasi device_id agar 1 perangkat tidak dapat dipakai untuk absensi user lain di hari yang sama.
     * Menangani UniqueConstraintViolationException jika terjadi request ganda serentak.
     *
     * @throws \RuntimeException
     */
    public function absen(User $user, ?string $ip = null, ?string $userAgent = null, ?string $deviceId = null): Absensi
    {
        $clientIp = trim($ip ?? request()->ip() ?? '');
        $agent = $userAgent ?? request()->userAgent();

        // 1. Validasi Jaringan WiFi Cabang
        if (! $this->jaringanValid($user, $clientIp)) {
            throw new \RuntimeException('Anda tidak terhubung ke jaringan WiFi cabang yang terdaftar.');
        }

        // 2. Cek apakah sudah pernah absen hari ini
        $existing = $this->statusHariIni($user);
        if ($existing) {
            return $existing;
        }

        $now = Carbon::now('Asia/Jakarta');

        // 3. Validasi Device-Lock (Anti-Titip Absen: 1 Perangkat hanya untuk 1 Staf per Hari)
        if (! empty($deviceId)) {
            $cleanDeviceId = trim(substr($deviceId, 0, 64));
            $existingDeviceAbsen = Absensi::whereDate('tanggal', $now->toDateString())
                ->where('device_id', $cleanDeviceId)
                ->where('user_id', '!=', $user->id)
                ->with('user')
                ->first();

            if ($existingDeviceAbsen) {
                $namaStaf = $existingDeviceAbsen->user?->name ?? 'staf lain';
                $jam = $existingDeviceAbsen->waktu_absen->format('H:i');
                throw new \RuntimeException("Perangkat ini sudah digunakan untuk absensi {$namaStaf} hari ini (jam {$jam} WIB). Satu perangkat hanya dapat digunakan oleh 1 staf per hari.");
            }
        }

        // 4. Evaluasi status keterlambatan & shift
        $evaluasi = $this->evaluasiKehadiran($now, $user->cabang_id);

        // 5. Catat absensi baru
        try {
            return Absensi::create([
                'cabang_id' => $user->cabang_id,
                'user_id' => $user->id,
                'tanggal' => $now->toDateString(),
                'waktu_absen' => $now,
                'shift' => $evaluasi['shift']->value,
                'status_kehadiran' => $evaluasi['status_kehadiran']->value,
                'menit_keterlambatan' => $evaluasi['menit_keterlambatan'],
                'snapshot_jam_masuk' => $evaluasi['snapshot_jam_masuk'],
                'snapshot_toleransi_menit' => $evaluasi['snapshot_toleransi_menit'],
                'ip_address' => $clientIp,
                'device_id' => ! empty($deviceId) ? trim(substr($deviceId, 0, 64)) : null,
                'user_agent' => $agent ? substr($agent, 0, 255) : null,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $existing = $this->statusHariIni($user);
            if ($existing) {
                return $existing;
            }
            throw $e;
        } catch (QueryException $e) {
            $existing = $this->statusHariIni($user);
            if ($existing) {
                return $existing;
            }
            throw $e;
        }
    }
}
