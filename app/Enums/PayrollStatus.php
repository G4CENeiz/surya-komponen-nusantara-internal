<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Finalized = 'finalized';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Finalized => 'Finalized',
        };
    }
}
