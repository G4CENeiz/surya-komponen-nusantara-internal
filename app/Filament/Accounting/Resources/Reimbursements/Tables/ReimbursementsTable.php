<?php

namespace App\Filament\Accounting\Resources\Reimbursements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReimbursementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable(),
                TextColumn::make('user.employee.department.name')
                    ->label('Dept')
                    ->searchable(),
                TextColumn::make('user.employee.jobClass.name')
                    ->label('Role')
                    ->searchable(),
                TextColumn::make('assignment.title')
                    ->label('Tugas')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('notes')
                    ->label('Keterangan')
                    ->limit(30),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->label('Status')
            ])
            ->actions([
                \Filament\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-information-circle')
                    ->color('info')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('Karyawan')
                            ->default(fn (\App\Models\Reimbursement $record) => $record->user->name ?? '-')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('Departemen')
                            ->default(fn (\App\Models\Reimbursement $record) => $record->user->employee->department->name ?? '-')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('Jabatan')
                            ->default(fn (\App\Models\Reimbursement $record) => $record->user->employee->jobClass->name ?? '-')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('Tugas')
                            ->default(fn (\App\Models\Reimbursement $record) => $record->assignment->title ?? '-')
                            ->disabled(),
                        \Filament\Forms\Components\TextInput::make('Nominal')
                            ->default(fn (\App\Models\Reimbursement $record) => 'Rp ' . number_format($record->amount, 0, ',', '.'))
                            ->disabled(),
                        \Filament\Forms\Components\Textarea::make('Keterangan / Tujuan')
                            ->default(fn (\App\Models\Reimbursement $record) => $record->notes)
                            ->disabled(),
                    ]),
                
                \Filament\Actions\Action::make('view_attachment')
                    ->label('Kuitansi')
                    ->icon('heroicon-o-document')
                    ->url(fn (\App\Models\Reimbursement $record): string => $record->getFirstMediaUrl('kuitansi') ?: \Illuminate\Support\Facades\Storage::url($record->attachment_path))
                    ->openUrlInNewTab(),
                
                \Filament\Actions\Action::make('approve')
                    ->label('ACC')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\Reimbursement $record): bool => $record->status === 'pending')
                    ->action(function (\App\Models\Reimbursement $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }),

                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn (\App\Models\Reimbursement $record): bool => $record->status === 'pending')
                    ->action(function (array $data, \App\Models\Reimbursement $record) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
