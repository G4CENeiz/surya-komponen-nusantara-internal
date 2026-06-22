<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PendingHr = 'pending_hr';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingHr => 'Pending HR Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingHr => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
