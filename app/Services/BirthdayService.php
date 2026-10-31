<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class BirthdayService
{
    protected string $timezone = 'Asia/Jakarta';

    /**
     * Dapatkan notifikasi ulang tahun terdekat (H-0 s/d H-7) untuk viewer.
     * Maksimal 1x per hari per viewer (ditandai oleh last_birthday_notif_at).
     */
    public function getNotification(User $viewer): array
    {
        $today = now($this->timezone)->startOfDay();

        // Cek apakah viewer sudah melihat notifikasi hari ini
        if ($viewer->last_birthday_notif_at && $viewer->last_birthday_notif_at->isSameDay($today)) {
            return [
                'phase' => null,
                'users' => [],
                'daysLeft' => null,
            ];
        }

        // Loop i = 0 (Hari H) sampai 7 (H-7)
        for ($i = 0; $i <= 7; $i++) {
            $targetDate = $today->copy()->addDays($i);
            $celebrants = $this->queryCelebrantsForDate($targetDate, $viewer->id);

            if ($celebrants->isNotEmpty()) {
                // Update timestamp notifikasi terakhir viewer tanpa mentrigger model event
                $viewer->last_birthday_notif_at = $today->toDateString();
                $viewer->saveQuietly();

                $usersData = $celebrants->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'bagian' => $u->bagian,
                ])->values()->all();

                return [
                    'phase' => $i === 0 ? 'today' : 'upcoming',
                    'users' => $usersData,
                    'daysLeft' => $i,
                ];
            }
        }

        return [
            'phase' => null,
            'users' => [],
            'daysLeft' => null,
        ];
    }

    /**
     * Dapatkan daftar staf yang berulang tahun hari ini (selain viewer).
     */
    public function getTodayCelebrants(?User $viewer = null): array
    {
        $today = now($this->timezone)->startOfDay();
        $excludeId = $viewer ? $viewer->id : null;

        return $this->queryCelebrantsForDate($today, $excludeId)->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'bagian' => $u->bagian,
        ])->values()->all();
    }

    /**
     * Cek apakah user yang diberikan berulang tahun hari ini.
     */
    public function isUserBirthdayToday(User $user): bool
    {
        if (! $user->tanggal_lahir || $user->trashed()) {
            return false;
        }

        $today = now($this->timezone)->startOfDay();
        $birthDate = Carbon::parse($user->tanggal_lahir);

        $targetMonth = $today->month;
        $targetDay = $today->day;
        $isNonLeapYear = ! $today->isLeapYear();

        // Cocokkan langsung bulan & hari
        if ($birthDate->month === $targetMonth && $birthDate->day === $targetDay) {
            return true;
        }

        // Kasus 29 Februari di tahun non-kabisat dirayakan 28 Februari
        if ($isNonLeapYear && $targetMonth === 2 && $targetDay === 28) {
            return $birthDate->month === 2 && $birthDate->day === 29;
        }

        return false;
    }

    /**
     * Query user aktif yang berulang tahun pada tanggal tertentu.
     */
    protected function queryCelebrantsForDate(Carbon $targetDate, ?int $excludeUserId = null)
    {
        $month = $targetDate->month;
        $day = $targetDate->day;
        $isNonLeapYear = ! $targetDate->isLeapYear();

        $query = User::query()
            ->whereNull('deleted_at')
            ->whereNotNull('tanggal_lahir');

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        $query->where(function ($sub) use ($month, $day, $isNonLeapYear) {
            $sub->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day);

            // Jika tanggal target adalah 28 Februari pada tahun non-kabisat, sertakan yang lahir 29 Februari
            if ($isNonLeapYear && $month === 2 && $day === 28) {
                $sub->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_lahir', 2)
                        ->whereDay('tanggal_lahir', 29);
                });
            }
        });

        return $query->orderBy('name')->get();
    }
}

