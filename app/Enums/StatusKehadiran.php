<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case TepatWaktu = 'tepat_waktu';
    case TerlaluAwal = 'terlalu_awal';
    case Terlambat = 'terlambat';
    case TerlambatKritis = 'terlambat_kritis';

    public function label(): string
    {
        return match ($this) {
            self::TepatWaktu => 'Tepat Waktu',
            self::TerlaluAwal => 'Lebih Awal',
            self::Terlambat => 'Terlambat',
            self::TerlambatKritis => 'Terlambat Kritis',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TepatWaktu => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::TerlaluAwal => 'bg-blue-50 text-[#0052FF] border-blue-200',
            self::Terlambat => 'bg-rose-50 text-rose-700 border-rose-200',
            self::TerlambatKritis => 'bg-amber-50 text-amber-700 border-amber-300',
        };
    }

    public function isTerlambat(): bool
    {
        return in_array($this, [self::Terlambat, self::TerlambatKritis], true);
    }
}
