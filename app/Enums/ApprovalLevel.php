<?php

namespace App\Enums;

enum ApprovalLevel: string
{
    case DiperiksaOleh = 'diperiksa_oleh';
    case DiketahuiOleh = 'diketahui_oleh';
    case DisetujuiOleh = 'disetujui_oleh';

    public function label(): string
    {
        return match ($this) {
            self::DiperiksaOleh => 'Diperiksa Oleh',
            self::DiketahuiOleh => 'Diketahui Oleh',
            self::DisetujuiOleh => 'Disetujui Oleh',
        };
    }
}
