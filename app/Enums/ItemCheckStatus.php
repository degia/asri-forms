<?php

namespace App\Enums;

enum ItemCheckStatus: string
{
    case Baik = 'baik';
    case TidakBaik = 'tidak_baik';

    public function label(): string
    {
        return match ($this) {
            self::Baik => 'Baik',
            self::TidakBaik => 'Tidak Baik',
        };
    }
}
