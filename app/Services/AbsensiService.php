<?php

namespace App\Services;

use App\Models\Absensi;
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

        return in_array($clientIp, $activeIps, true);
    }

    /**
     * Lakukan pencatatan absensi masuk untuk user.
     * Waktu diset ke Asia/Jakarta. Validasi IP dilakukan langsung di dalam service.
     * Menangani UniqueConstraintViolationException jika terjadi request ganda serentak.
     *
     * @throws \RuntimeException
     */
    public function absen(User $user, ?string $ip = null, ?string $userAgent = null): Absensi
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

        // 3. Catat absensi baru
        $now = Carbon::now('Asia/Jakarta');

        try {
            return Absensi::create([
                'cabang_id' => $user->cabang_id,
                'user_id' => $user->id,
                'tanggal' => $now->toDateString(),
                'waktu_absen' => $now,
                'ip_address' => $clientIp,
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
