<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AttendanceTrendChart extends ChartWidget
{
    protected ?string $heading = 'Tren Kehadiran (7 Hari Terakhir)';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $labels = [];
        $present = [];
        $late = [];
        $absent = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');

            $dayAttendances = Attendance::whereDate('date', $date->toDateString())->get();

            $present[] = $dayAttendances->whereNotNull('clock_in_at')->where('is_late', false)->count();
            $late[] = $dayAttendances->where('is_late', true)->count();
            $absent[] = max(0, User::whereHas('roles', fn ($q) => $q->where('name', 'employee'))->count() - $dayAttendances->count());
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tepat Waktu',
                    'data' => $present,
                    'backgroundColor' => '#22c55e',
                    'borderColor' => '#22c55e',
                ],
                [
                    'label' => 'Terlambat',
                    'data' => $late,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Tidak Hadir',
                    'data' => $absent,
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
