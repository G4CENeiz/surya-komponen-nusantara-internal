<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Announcement;
use App\Models\Employee;
use App\Models\JobClass;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrdOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeEmployees = Employee::where('status', 'active')->count();
        $totalJobClasses = JobClass::count();
        $totalAnnouncements = Announcement::where('is_active', true)->count();

        return [
            Stat::make('Total Employees', $activeEmployees)
                ->description('Active employees')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Total Job Classes', $totalJobClasses)
                ->description('Salary grades defined')
                ->icon('heroicon-o-briefcase')
                ->color('primary'),
            Stat::make('Total Announcements', $totalAnnouncements)
                ->description('Active announcements')
                ->icon('heroicon-o-megaphone')
                ->color('primary'),
        ];
    }
}
