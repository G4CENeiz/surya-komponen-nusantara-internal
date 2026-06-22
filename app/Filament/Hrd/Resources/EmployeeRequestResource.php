<?php

namespace App\Filament\Hrd\Resources;

use App\Enums\SubmissionStatus;
use App\Enums\SubmissionType;
use App\Filament\Hrd\Resources\EmployeeRequestResource\Pages;
use App\Models\Submission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EmployeeRequestResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Employee Requests';

    protected static ?string $modelLabel = 'Employee Request';

    protected static ?string $pluralModelLabel = 'Employee Requests';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Employee'),
                Select::make('type')
                    ->options(SubmissionType::class)
                    ->required()
                    ->label('Type'),
                DatePicker::make('start_date')
                    ->required()
                    ->label('Start Date'),
                DatePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date')
                    ->label('End Date'),
                Textarea::make('reason')
                    ->rows(3)
                    ->maxLength(1000)
                    ->label('Reason'),
                FileUpload::make('doctor_letter_path')
                    ->label('Medical Letter (Surat Dokter)')
                    ->disk('public')
                    ->directory('doctor-letters')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(5120)
                    ->visible(fn ($record): bool => $record?->type === SubmissionType::Sick)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (SubmissionType $state): string => $state->label())
                    ->color(fn (SubmissionType $state): string => $state->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Start Date'),
                Tables\Columns\TextColumn::make('end_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('End Date'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->reason)
                    ->label('Reason'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (SubmissionStatus $state): string => $state->label())
                    ->color(fn (SubmissionStatus $state): string => $state->color())
                    ->sortable()
                    ->label('Status'),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Reviewed At'),
                Tables\Columns\TextColumn::make('review_notes')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->review_notes)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Review Notes'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(SubmissionType::class)
                    ->label('Type'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(SubmissionStatus::class)
                    ->label('Status'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Request')
                    ->modalDescription('Are you sure you want to approve this request? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, Approve')
                    ->action(fn ($record) => $record->update([
                        'status' => SubmissionStatus::Approved,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]))
                    ->visible(fn ($record): bool => $record->status === SubmissionStatus::Pending),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->modalHeading('Reject Request')
                    ->modalDescription('Please provide a reason for rejecting this request.')
                    ->modalSubmitActionLabel('Yes, Reject')
                    ->form([
                        Textarea::make('review_notes')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Explain why this request is being rejected...'),
                    ])
                    ->action(fn ($record, array $data) => $record->update([
                        'status' => SubmissionStatus::Rejected,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                        'review_notes' => $data['review_notes'],
                    ]))
                    ->visible(fn ($record): bool => $record->status === SubmissionStatus::Pending),
                Action::make('viewAttachment')
                    ->label('View Attachment')
                    ->icon('heroicon-o-document')
                    ->color('primary')
                    ->url(fn ($record): ?string => $record->doctor_letter_path
                        ? Storage::disk('public')->url($record->doctor_letter_path)
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => $record->type === SubmissionType::Sick
                        && filled($record->doctor_letter_path)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeRequests::route('/'),
        ];
    }
}
