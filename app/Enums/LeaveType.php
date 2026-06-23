<?php

namespace App\Enums;

enum LeaveType: string
{
    case AnnualLeave = 'annual_leave';
    case SickLeave = 'sick_leave';
    case Overtime = 'overtime';
    case MaternityLeave = 'maternity_leave';
    case MarriageLeave = 'marriage_leave';
    case BereavementLeave = 'bereavement_leave';
    case PersonalLeave = 'personal_leave';

    public function label(): string
    {
        return match ($this) {
            self::AnnualLeave => 'Cuti Tahunan',
            self::SickLeave => 'Sakit',
            self::Overtime => 'Lembur',
            self::MaternityLeave => 'Cuti Melahirkan',
            self::MarriageLeave => 'Cuti Menikah',
            self::BereavementLeave => 'Cuti Kematian Keluarga',
            self::PersonalLeave => 'Cuti Pribadi',
        };
    }
}
