<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case TepatWaktu = 'tepat_waktu';
    case Terlambat = 'terlambat';

    public function label(): string
    {
        return match ($this) {
            self::TepatWaktu => 'Tepat Waktu',
            self::Terlambat => 'Terlambat',
        };
    }
}

