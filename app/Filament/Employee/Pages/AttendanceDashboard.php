<?php

namespace App\Filament\Employee\Pages;

use App\Enums\AttendanceStatus;
use App\Filament\Employee\Widgets\LeaderboardTable;
use App\Models\Attendance as AttendanceModel;
use App\Services\AttendanceService;
use App\Services\DeepFaceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceDashboard extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Attendance Dashboard';

    protected static ?string $title = 'Attendance Dashboard';

    public bool $canClockIn = false;

    public bool $canClockOut = false;

    public ?array $todayAttendance = null;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function content(Schema $schema): Schema
    {
        $att = $this->todayAttendance ?? [];

        return $schema->components([
            Grid::make(2)
                ->schema([
                    Section::make('Today\'s Attendance')
                        ->columnSpan(1)
                        ->schema([
                            Html::make(view('filament.employee.partials.clock')->render()),

                            $this->makeStatsGrid($att),

                            Actions::make([
                                $this->getClockInAction(),
                                $this->getClockOutAction(),
                            ])->alignCenter(),
                        ]),

                    Section::make('Today\'s Leaderboard')
                        ->columnSpan(1)
                        ->schema([
                            EmbeddedTable::make(LeaderboardTable::class),
                        ]),
                ]),

            Section::make('My Attendance History')
                ->schema([
                    EmbeddedTable::make(),
                ]),
        ]);
    }

    private function makeStatsGrid(array $att): Grid
    {
        $clockIn = $att['clock_in_at'] ?? '—';
        $clockOut = $att['clock_out_at'] ?? '—';
        $status = ($att['status'] ?? null) instanceof AttendanceStatus ? $att['status'] : null;
        $geofence = $att['clock_in_within_geofence'] ?? null;
        $hours = $att['worked_hours'] ?? null;
        $isLate = $att['is_late'] ?? false;

        $geoText = match ($geofence) {
            true => 'Inside',
            false => 'Outside',
            default => '—',
        };

        $statusText = $status?->label() ?? '—';

        $hoursText = $hours ? number_format((float) $hours, 1).'h' : '—';

        return Grid::make(2)
            ->schema([
                // Row 1: Clock In | Clock Out
                Text::make('Clock In')
                    ->size(TextSize::ExtraSmall)
                    ->color('gray'),
                Text::make('Clock Out')
                    ->size(TextSize::ExtraSmall)
                    ->color('gray'),

                // Row 2: values
                $isLate
                    ? Text::make('Late · '.$clockIn)->badge()->color('danger')
                    : Text::make($clockIn)->weight(FontWeight::SemiBold),
                Text::make($clockOut)
                    ->weight(FontWeight::SemiBold),

                // Row 3: Geofence | Status
                Text::make('Geofence')
                    ->size(TextSize::ExtraSmall)
                    ->color('gray'),
                Text::make('Status')
                    ->size(TextSize::ExtraSmall)
                    ->color('gray'),

                // Row 4: values (badges)
                Text::make($geoText)
                    ->badge()
                    ->color(fn () => match ($geofence) {
                        true => 'success',
                        false => 'danger',
                        default => 'gray',
                    }),
                Text::make($statusText)
                    ->badge()
                    ->color(fn () => match ($status) {
                        AttendanceStatus::Approved => 'success',
                        AttendanceStatus::Rejected => 'danger',
                        default => 'warning',
                    }),

                // Row 5: Hours
                Text::make('Hours')
                    ->size(TextSize::ExtraSmall)
                    ->color('gray'),
                Text::make($hoursText)
                    ->weight(FontWeight::SemiBold),
            ]);
    }

    protected function getClockInAction(): Action
    {
        return Action::make('clockIn')
            ->label('Clock In')
            ->icon('heroicon-m-arrow-right-circle')
            ->color('success')
            ->disabled(fn () => ! $this->canClockIn)
            ->modalHeading('Clock In')
            ->modalSubmitActionLabel('Confirm Clock In')
            ->modalContent(view('filament.employee.partials.location-ip-info'))
            ->form([
                FileUpload::make('face_photo')
                    ->label('Face Photo')
                    ->disk('public')
                    ->directory('attendance/face-photos')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->extraInputAttributes(['capture' => 'environment'])
                    ->columnSpanFull()
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = auth()->user();
                $facePhoto = $data['face_photo'] ?? null;

                // Verify face if user has a registered photo
                if ($user->face_photo && $facePhoto) {
                    $deepFace = app(DeepFaceService::class);
                    $verification = $deepFace->verifyUser($facePhoto, $user->face_photo);

                    if (! $verification['verified']) {
                        Notification::make()
                            ->title('Face verification failed')
                            ->body($verification['message'])
                            ->danger()
                            ->send();

                        return;
                    }
                } elseif (! $user->face_photo && $facePhoto) {
                    // First time — register the face
                    $user->update(['face_photo' => $facePhoto]);
                }

                $result = app(AttendanceService::class)->clockIn(
                    user: $user,
                    lat: 0,
                    lng: 0,
                    request: request(),
                );

                $result['success']
                    ? Notification::make()->title($result['message'])->success()->send()
                    : Notification::make()->title($result['message'])->danger()->send();

                $this->refreshStatus();
            });
    }

    protected function getClockOutAction(): Action
    {
        return Action::make('clockOut')
            ->label('Clock Out')
            ->icon('heroicon-m-arrow-left-circle')
            ->color('danger')
            ->disabled(fn () => ! $this->canClockOut)
            ->modalHeading('Clock Out')
            ->modalSubmitActionLabel('Confirm Clock Out')
            ->modalContent(view('filament.employee.partials.location-ip-info'))
            ->form([
                FileUpload::make('face_photo')
                    ->label('Face Photo')
                    ->disk('public')
                    ->directory('attendance/face-photos')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->extraInputAttributes(['capture' => 'environment'])
                    ->columnSpanFull()
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = auth()->user();
                $facePhoto = $data['face_photo'] ?? null;

                // Verify face if user has a registered photo
                if ($user->face_photo && $facePhoto) {
                    $deepFace = app(DeepFaceService::class);
                    $verification = $deepFace->verifyUser($facePhoto, $user->face_photo);

                    if (! $verification['verified']) {
                        Notification::make()
                            ->title('Face verification failed')
                            ->body($verification['message'])
                            ->danger()
                            ->send();

                        return;
                    }
                }

                $result = app(AttendanceService::class)->clockOut(
                    user: $user,
                    lat: 0,
                    lng: 0,
                    request: request(),
                );

                $result['success']
                    ? Notification::make()->title($result['message'])->success()->send()
                    : Notification::make()->title($result['message'])->danger()->send();

                $this->refreshStatus();
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AttendanceModel::where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in_at')
                    ->dateTime('H:i:s')
                    ->label('Clock In'),
                Tables\Columns\TextColumn::make('clock_out_at')
                    ->dateTime('H:i:s')
                    ->label('Clock Out'),
                Tables\Columns\TextColumn::make('worked_hours')
                    ->suffix('h')
                    ->label('Hours'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        AttendanceStatus::PendingHr => 'Pending HR',
                        AttendanceStatus::Approved => 'Approved',
                        AttendanceStatus::Rejected => 'Rejected',
                        default => ucfirst(str_replace('_', ' ', $state->value ?? (string) $state)),
                    })
                    ->color(fn ($state): string => match ($state) {
                        AttendanceStatus::Approved => 'success',
                        AttendanceStatus::Rejected => 'danger',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50]);
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
        } else {
            $this->todayAttendance = null;
        }

        $this->canClockIn = ! $today || ! $today->clock_in_at;
        $this->canClockOut = $today && $today->clock_in_at && ! $today->clock_out_at;
    }
}
