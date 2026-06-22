<?php

namespace App\Enums;

enum LeaveType: string
{
    case AnnualLeave = 'annual_leave';
    case SickLeave = 'sick_leave';
    case Overtime = 'overtime';

    public function label(): string
    {
        return match ($this) {
            self::AnnualLeave => 'Cuti Tahunan',
            self::SickLeave => 'Surat Sakit',
            self::Overtime => 'Lembur',
        };
    }
}
