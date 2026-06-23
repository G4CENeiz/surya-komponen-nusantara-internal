<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Overtime;
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
        $today = now()->toDateString();

        $totalEmployees = Employee::where('status', 'active')->count();

        // Present today (has clock_in_at)
        $todayQuery = Attendance::whereDate('date', $today);
        if ($dateFrom) {
            $todayQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $todayQuery->whereDate('date', '<=', $dateTo);
        }
        $presentToday = (clone $todayQuery)->whereNotNull('clock_in_at')->count();
        $lateToday = (clone $todayQuery)->where('is_late', true)->count();

        // On leave today
        $onLeave = LeaveRequest::where('status', 'approved')
            ->where('type', 'annual_leave')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        // Sick today
        $sick = LeaveRequest::where('status', 'approved')
            ->where('type', 'sick_leave')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        // Overtime today
        $onOvertime = Overtime::where('date', $today)
            ->count();

        // Pending leave requests
        $pendingApprovals = LeaveRequest::where('status', 'pending')->count();

        // Suspicious attendances
        $suspicious = Attendance::where('is_suspicious', true)->count();

        return [
            Stat::make('Total Karyawan', $totalEmployees)
                ->description($totalEmployees.' karyawan aktif')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Hadir Hari Ini', $presentToday)
                ->description($presentToday.' dari '.$totalEmployees.' sudah check in')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Cuti', $onLeave)
                ->description($onLeave.' cuti disetujui')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Sakit', $sick)
                ->description($sick.' surat sakit disetujui')
                ->descriptionIcon('heroicon-o-heart')
                ->color('danger'),

            Stat::make('Menunggu Persetujuan', $pendingApprovals)
                ->description($pendingApprovals > 0 ? $pendingApprovals.' perlu ditinjau' : 'Semua sudah ditinjau!')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color($pendingApprovals > 0 ? 'warning' : 'success'),

            Stat::make('Absensi Mencurigakan', $suspicious)
                ->description($suspicious > 0 ? $suspicious.' foto perlu diverifikasi' : 'Semua aman')
                ->descriptionIcon('heroicon-o-shield-exclamation')
                ->color($suspicious > 0 ? 'danger' : 'success'),
        ];
    }
}
