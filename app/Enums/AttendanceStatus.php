<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Leave = 'leave';
    case OnLeave = 'on_leave';
    case Sick = 'sick';
    case FieldDuty = 'field_duty';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Leave => 'Leave',
            self::OnLeave => 'On Leave',
            self::Sick => 'Sick',
            self::FieldDuty => 'Field Duty',
            self::Absent => 'Absent',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Leave => 'info',
            self::OnLeave => 'warning',
            self::Sick => 'danger',
            self::FieldDuty => 'info',
            self::Absent => 'danger',
        };
    }
}
