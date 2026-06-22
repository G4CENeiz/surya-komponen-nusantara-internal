<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrdStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $totalEmployees = User::whereHas('roles', fn ($q) => $q->where('name', 'employee'))->count();

        $presentToday = Attendance::whereDate('date', $today)
            ->whereNotNull('clock_in_at')
            ->count();

        $lateToday = Attendance::whereDate('date', $today)
            ->where('is_late', true)
            ->count();

        $pendingRequests = LeaveRequest::where('status', 'pending')->count();

        $onLeave = LeaveRequest::where('status', 'approved')
            ->where('type', 'annual_leave')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        $sickToday = LeaveRequest::where('status', 'approved')
            ->where('type', 'sick_leave')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        $suspiciousAttendances = Attendance::where('is_suspicious', true)->count();

        return [
            Stat::make('Total Pegawai', $totalEmployees)
                ->description('Karyawan aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Hadir Hari Ini', $presentToday." / {$totalEmployees}")
                ->description($lateToday > 0 ? "{$lateToday} terlambat" : 'Semua tepat waktu')
                ->descriptionIcon($lateToday > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($lateToday > 0 ? 'warning' : 'success'),

            Stat::make('Cuti / Sakit', "{$onLeave} cuti · {$sickToday} sakit")
                ->description('Sedang tidak hadir')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Menunggu Persetujuan', $pendingRequests)
                ->description('Pengajuan cuti/sakit/lembur')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($pendingRequests > 0 ? 'danger' : 'success'),

            Stat::make('Absensi Mencurigakan', $suspiciousAttendances)
                ->description('Foto perlu diverifikasi')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($suspiciousAttendances > 0 ? 'danger' : 'success'),

            Stat::make('Tugas Luar Hari Ini', Attendance::whereDate('date', $today)->where('clock_in_method', 'manual')->count())
                ->description('Absensi di luar geofence')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),
        ];
    }
}
