<?php

namespace App\Enums;

enum FormStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Diketahui = 'diketahui';
    case Disetujui = 'disetujui';
    case Selesai = 'selesai';
    case Revisi = 'revisi';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Diketahui => 'Diketahui',
            self::Disetujui => 'Disetujui',
            self::Selesai => 'Selesai',
            self::Revisi => 'Revisi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Diketahui => 'yellow',
            self::Disetujui => 'green',
            self::Selesai => 'emerald',
            self::Revisi => 'red',
        };
    }
}
