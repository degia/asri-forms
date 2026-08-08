<?php

namespace App\Enums;

enum KondisiPerawatan: string
{
    case Good = 'good';
    case Fair = 'fair';
    case Critical = 'critical';
    case Poor = 'poor';

    public function label(): string
    {
        return match ($this) {
            self::Good => 'Good',
            self::Fair => 'Fair',
            self::Critical => 'Critical',
            self::Poor => 'Poor',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Good => 'emerald',
            self::Fair => 'blue',
            self::Critical => 'amber',
            self::Poor => 'red',
        };
    }
}
