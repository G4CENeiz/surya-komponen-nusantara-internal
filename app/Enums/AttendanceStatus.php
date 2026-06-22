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
    case PendingHr = 'pending_hr';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Leave => 'Leave',
            self::OnLeave => 'On Leave',
            self::Sick => 'Sick',
            self::FieldDuty => 'Field Duty',
            self::Absent => 'Absent',
            self::PendingHr => 'Pending HR Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
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
            self::PendingHr => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
