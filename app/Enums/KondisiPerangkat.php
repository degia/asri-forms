<?php

namespace App\Enums;

enum KondisiPerangkat: string
{
    case Baru = 'baru';
    case Lama = 'lama';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Lama => 'Lama',
        };
    }
}
