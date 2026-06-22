<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Submission;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrdOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 6;
    }

    protected function getStats(): array
    {
        $dateFrom = $this->pageFilters['dateFrom'] ?? null;
        $dateTo = $this->pageFilters['dateTo'] ?? null;

        $totalEmployees = Employee::where('status', 'active')->count();

        $todayQuery = Attendance::whereDate('date', now()->toDateString());
        if ($dateFrom) {
            $todayQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $todayQuery->whereDate('date', '<=', $dateTo);
        }
        $presentToday = (clone $todayQuery)->where('status', 'approved')->count();

        $leaveQuery = Submission::where('type', 'leave')->where('status', 'approved');
        if ($dateFrom) {
            $leaveQuery->where('start_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $leaveQuery->where('end_date', '<=', $dateTo);
        }
        $onLeave = (clone $leaveQuery)->count();

        $sickQuery = Submission::where('type', 'sick')->where('status', 'approved');
        if ($dateFrom) {
            $sickQuery->where('start_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $sickQuery->where('end_date', '<=', $dateTo);
        }
        $sick = (clone $sickQuery)->count();

        $overtimeQuery = Submission::where('type', 'overtime')->where('status', 'approved');
        if ($dateFrom) {
            $overtimeQuery->where('overtime_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $overtimeQuery->where('overtime_date', '<=', $dateTo);
        }
        $onOvertime = (clone $overtimeQuery)->count();

        $pendingApprovals = Submission::where('status', 'pending')->count();

        return [
            Stat::make('Total Employees', $totalEmployees)
                ->description($totalEmployees.' active staff')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 8, 7]),

            Stat::make('Present Today', $presentToday)
                ->description($presentToday.' of '.$totalEmployees.' checked in')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success')
                ->chart([$presentToday, $presentToday - 1, $presentToday + 2, $presentToday - 3, $presentToday + 1, $presentToday + 2, $presentToday]),

            Stat::make('On Leave', $onLeave)
                ->description($onLeave.' approved leaves')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Sick', $sick)
                ->description($sick.' approved sick leave')
                ->descriptionIcon('heroicon-o-heart')
                ->color('danger'),

            Stat::make('Overtime', $onOvertime)
                ->description($onOvertime.' approved overtime')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Pending', $pendingApprovals)
                ->description($pendingApprovals > 0 ? $pendingApprovals.' need review' : 'All caught up!')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color($pendingApprovals > 0 ? 'warning' : 'success'),
        ];
    }
}
