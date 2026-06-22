<?php

namespace App\Filament\Employee\Pages;

use App\Enums\AttendanceStatus;
use App\Models\Attendance as AttendanceModel;
use App\Services\AttendanceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Attendance extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Clock In / Out';

    protected static ?string $title = 'Attendance';

    public bool $canClockIn = false;

    public bool $canClockOut = false;

    public ?array $todayAttendance = null;

    public ?array $leaderboard = null;

    public ?array $attendanceHistory = null;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function content(Schema $schema): Schema
    {
        $att = $this->todayAttendance ?? [];
        $clockIn = $att['clock_in_at'] ?? '—';
        $clockOut = $att['clock_out_at'] ?? '—';
        $status = ($att['status'] ?? null) instanceof AttendanceStatus
            ? $att['status']->value
            : ($att['status'] ?? '');
        $geofence = $att['clock_in_within_geofence'] ?? null;
        $hours = $att['worked_hours'] ?? null;
        $isLate = $att['is_late'] ?? false;

        $statusColors = ['pending_hr' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
        $statusColor = $statusColors[$status] ?? 'gray';
        $geoColor = match ($geofence) {
            true => 'success', false => 'danger', default => 'gray'
        };
        $geoText = match ($geofence) {
            true => 'Inside', false => 'Outside', default => '—'
        };

        // Build the full Today's Attendance section with clock + grid + buttons
        $todayHtml = '<div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">';

        // Big clock
        $todayHtml .= '<div style="text-align: center;">';
        $todayHtml .= '<div x-data="{ time: \'--:--:-- --\' }" x-init="const u = () => { const n = new Date(); time = n.toLocaleTimeString(\'en-US\', { hour: \'2-digit\', minute: \'2-digit\', second: \'2-digit\', hour12: true }); }; u(); setInterval(u, 1000);" style="font-size: 3rem; font-family: monospace; font-weight: 700; letter-spacing: 0.05em; color: inherit;" x-text="time"></div>';
        $todayHtml .= '<div style="font-size: 0.875rem; opacity: 0.6; margin-top: 0.5rem;">' . now()->format('l, F j, Y') . '</div>';
        $todayHtml .= '</div>';

        // Stats grid
        $todayHtml .= '<div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; width: 100%; text-align: center;">';

        $items = [
            ['label' => 'Clock In', 'value' => $clockIn, 'badge' => $isLate ? $this->badge('Late', 'danger', true) : ''],
            ['label' => 'Geofence', 'value' => '', 'badge' => $this->badge($geoText, $geoColor, true)],
            ['label' => 'Clock Out', 'value' => $clockOut, 'badge' => ''],
            ['label' => 'Status', 'value' => '', 'badge' => $this->badge(ucfirst(str_replace('_', ' ', $status)), $statusColor, true)],
            ['label' => 'Hours', 'value' => $hours ?? '—', 'badge' => ''],
        ];

        foreach ($items as $item) {
            $todayHtml .= '<div>';
            $todayHtml .= '<div style="font-size: 0.75rem; opacity: 0.6; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em;">' . $item['label'] . '</div>';
            if ($item['badge'] !== '') {
                $todayHtml .= '<div style="margin-top: 0.25rem;">' . $item['badge'] . '</div>';
            } else {
                $todayHtml .= '<div style="font-size: 1.125rem; font-weight: 600;">' . $item['value'] . '</div>';
            }
            $todayHtml .= '</div>';
        }

        $todayHtml .= '</div>';

        // Buttons
        $clockInDisabled = $this->canClockIn ? '' : 'opacity: 0.5; cursor: not-allowed;';
        $clockOutDisabled = $this->canClockOut ? '' : 'opacity: 0.5; cursor: not-allowed;';

        $todayHtml .= '<div style="display: flex; justify-content: center; gap: 1rem; width: 100%;">';
        $todayHtml .= '<button wire:click="mountAction(\'clock-in\')" style="' . $clockInDisabled . 'display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; color: white; background-color: #22c55e; transition: background-color 0.15s;">';
        $todayHtml .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>';
        $todayHtml .= 'Clock In</button>';
        $todayHtml .= '<button wire:click="mountAction(\'clock-out\')" style="' . $clockOutDisabled . 'display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500; color: white; background-color: #ef4444; transition: background-color 0.15s;">';
        $todayHtml .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15m-3 0l-3-3m0 0l3-3m-3 3H9" /></svg>';
        $todayHtml .= 'Clock Out</button>';
        $todayHtml .= '</div>';

        $todayHtml .= '</div>';

        // Build leaderboard HTML
        $leaderboardHtml = '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        if (empty($this->leaderboard)) {
            $leaderboardHtml .= '<div style="font-size: 0.875rem; color: #6b7280; text-align: center; padding: 1rem 0;">No attendance records yet today</div>';
        } else {
            $rank = 1;
            foreach ($this->leaderboard as $entry) {
                $isCurrentUser = $entry['user_id'] === auth()->id();
                $nameStyle = $isCurrentUser ? 'font-weight: 700; color: #2563eb;' : 'color: #111827;';
                $bgStyle = $isCurrentUser ? 'background-color: #eff6ff;' : '';
                $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                $medal = $medals[$rank] ?? ($rank . '.');
                $lateBadge = $entry['is_late'] ? $this->badge('Late', 'danger', true) : '';
                $name = e($entry['name']);
                $clockInTime = e($entry['clock_in_at']);
                $leaderboardHtml .= '<div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem; border-radius: 0.5rem; ' . $bgStyle . '">';
                $leaderboardHtml .= '<div style="display: flex; align-items: center; gap: 0.5rem;">';
                $leaderboardHtml .= '<span style="font-size: 0.875rem; width: 2rem;">' . $medal . '</span>';
                $leaderboardHtml .= '<span style="font-size: 0.875rem; ' . $nameStyle . '">' . $name . '</span>';
                $leaderboardHtml .= $lateBadge;
                $leaderboardHtml .= '</div>';
                $leaderboardHtml .= '<span style="font-size: 0.875rem; color: #6b7280;">' . $clockInTime . '</span>';
                $leaderboardHtml .= '</div>';
                $rank++;
            }
        }
        $leaderboardHtml .= '</div>';

        return $schema->components([
            // Top row: Today's Attendance (with clock) + Leaderboard
            Grid::make(2)
                ->schema([
                    Section::make('Today\'s Attendance')
                        ->schema([
                            Html::make($todayHtml),
                        ])
                        ->columnSpan(1),

                    Section::make('Today\'s Leaderboard')
                        ->schema([
                            Html::make($leaderboardHtml),
                        ])
                        ->columnSpan(1),
                ]),

            // Bottom: Attendance history
            Section::make('My Attendance History')
                ->schema([
                    Html::make(view('filament.employee.partials.attendance-history', [
                        'records' => $this->attendanceHistory,
                    ])->render()),
                ]),
        ]);
    }

    private function badge(string $label, string $color, bool $show): string
    {
        if (! $show) {
            return '';
        }

        $colors = [
            'success' => ['bg' => 'rgba(34, 197, 94, 0.15)', 'text' => '#22c55e'],
            'danger' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#ef4444'],
            'warning' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#f59e0b'],
            'gray' => ['bg' => 'rgba(107, 114, 128, 0.15)', 'text' => '#6b7280'],
        ];

        $c = $colors[$color] ?? $colors['gray'];

        return '<span style="display: inline-flex; align-items: center; border-radius: 0.375rem; padding: 0.125rem 0.5rem; font-size: 0.75rem; font-weight: 500; background-color: '.$c['bg'].'; color: '.$c['text'].';">'.e($label).'</span>';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function refreshStatus(): void
    {
        $user = auth()->user();
        $attendanceService = app(AttendanceService::class);
        $today = $attendanceService->getTodayStatus($user);

        if ($today) {
            $this->todayAttendance = [
                'clock_in_at' => $today->clock_in_at?->format('H:i:s'),
                'clock_in_within_geofence' => $today->clock_in_within_geofence,
                'clock_out_at' => $today->clock_out_at?->format('H:i:s'),
                'clock_out_within_geofence' => $today->clock_out_within_geofence,
                'status' => $today->status,
                'worked_hours' => $today->worked_hours,
                'is_late' => $today->is_late,
            ];
        }

        $this->canClockIn = ! $today || ! $today->clock_in_at;
        $this->canClockOut = $today && $today->clock_in_at && ! $today->clock_out_at;

        // Leaderboard: today's attendance sorted by clock_in_at
        $this->leaderboard = AttendanceModel::whereDate('date', now()->toDateString())
            ->whereNotNull('clock_in_at')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->orderBy('attendances.clock_in_at', 'asc')
            ->select('attendances.*', 'users.name')
            ->get()
            ->map(fn ($record) => [
                'user_id' => $record->user_id,
                'name' => $record->name,
                'clock_in_at' => $record->clock_in_at->format('H:i:s'),
                'is_late' => $record->is_late,
            ])
            ->toArray();

        // History: current user's last 30 attendance records
        $this->attendanceHistory = AttendanceModel::where('user_id', $user->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->map(fn ($record) => [
                'date' => $record->date->format('M d, Y'),
                'clock_in_at' => $record->clock_in_at?->format('H:i:s'),
                'clock_out_at' => $record->clock_out_at?->format('H:i:s'),
                'worked_hours' => $record->worked_hours,
                'status' => $record->status->value,
            ])
            ->toArray();
    }

    public function handleClockIn(): void
    {
        $lat = (float) session('attendance_lat', 0);
        $lng = (float) session('attendance_lng', 0);
        $gpsAccuracy = (float) session('attendance_gps_accuracy', 0);
        $gpsSpeed = (float) session('attendance_gps_speed', 0);

        $result = app(AttendanceService::class)->clockIn(
            user: auth()->user(),
            lat: $lat,
            lng: $lng,
            request: request(),
            gpsAccuracy: $gpsAccuracy,
            gpsSpeed: $gpsSpeed,
        );

        $result['success']
            ? Notification::make()->title($result['message'])->success()->send()
            : Notification::make()->title($result['message'])->danger()->send();

        $this->refreshStatus();
    }

    public function handleClockOut(): void
    {
        $lat = (float) session('attendance_lat', 0);
        $lng = (float) session('attendance_lng', 0);
        $gpsAccuracy = (float) session('attendance_gps_accuracy', 0);
        $gpsSpeed = (float) session('attendance_gps_speed', 0);

        $result = app(AttendanceService::class)->clockOut(
            user: auth()->user(),
            lat: $lat,
            lng: $lng,
            request: request(),
            gpsAccuracy: $gpsAccuracy,
            gpsSpeed: $gpsSpeed,
        );

        $result['success']
            ? Notification::make()->title($result['message'])->success()->send()
            : Notification::make()->title($result['message'])->danger()->send();

        $this->refreshStatus();
    }

    public function storeGpsData(float $lat, float $lng, float $accuracy, float $speed): void
    {
        session([
            'attendance_lat' => $lat,
            'attendance_lng' => $lng,
            'attendance_gps_accuracy' => $accuracy,
            'attendance_gps_speed' => $speed,
        ]);
    }
}
