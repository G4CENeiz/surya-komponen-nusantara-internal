<?php

namespace App\Enums;

enum SubmissionType: string
{
    case Leave = 'leave';
    case Sick = 'sick';
    case Overtime = 'overtime';

    public function label(): string
    {
        return match ($this) {
            self::Leave => 'Cuti',
            self::Sick => 'Sakit',
            self::Overtime => 'Lembur',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Leave => 'info',
            self::Sick => 'warning',
            self::Overtime => 'success',
        };
    }
}
