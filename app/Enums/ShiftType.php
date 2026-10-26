<?php

namespace App\Enums;

enum ShiftType: string
{
    case Pagi = 'pagi';
    case Sore = 'sore';

    public function label(): string
    {
        return match ($this) {
            self::Pagi => 'Pagi',
            self::Sore => 'Sore',
        };
    }
}

