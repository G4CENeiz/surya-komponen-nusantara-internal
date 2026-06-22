<?php

namespace App\Filament\Hrd\Resources;

use App\Filament\Hrd\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Approvals';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $navigationLabel = 'Leave / Sick / Overtime';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('review_notes')
                    ->label('Review Notes')
                    ->rows(3)
                    ->placeholder('Add notes for this decision...'),
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
                Tables\Columns\TextColumn::make('employee.nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'leave' => 'info',
                        'sick' => 'warning',
                        'overtime' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'leave' => 'Leave (Cuti)',
                        'sick' => 'Sickness (Sakit)',
                        'overtime' => 'Overtime (Lembur)',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'leave' => 'Leave (Cuti)',
                        'sick' => 'Sickness (Sakit)',
                        'overtime' => 'Overtime (Lembur)',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Submission')
                    ->modalDescription('Are you sure you want to approve this submission?')
                    ->form([
                        Textarea::make('review_notes')
                            ->label('Review Notes')
                            ->rows(2)
                            ->placeholder('Optional notes...'),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'] ?? null,
                        ]);
                    })
                    ->visible(fn (Submission $record): bool => $record->status === 'pending'),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Submission')
                    ->modalDescription('Are you sure you want to reject this submission?')
                    ->form([
                        Textarea::make('review_notes')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->required()
                            ->placeholder('Please provide a reason...'),
                    ])
                    ->action(function (Submission $record, array $data): void {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_notes' => $data['review_notes'],
                        ]);
                    })
                    ->visible(fn (Submission $record): bool => $record->status === 'pending'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'view' => Pages\ViewSubmission::route('/{record}'),
        ];
    }
}
