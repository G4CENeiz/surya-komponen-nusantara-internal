<?php

namespace App\Filament\Accounting\Resources\Reimbursements;

use App\Filament\Accounting\Resources\Reimbursements\Pages\CreateReimbursement;
use App\Filament\Accounting\Resources\Reimbursements\Pages\EditReimbursement;
use App\Filament\Accounting\Resources\Reimbursements\Pages\ListReimbursements;
use App\Filament\Accounting\Resources\Reimbursements\Schemas\ReimbursementForm;
use App\Filament\Accounting\Resources\Reimbursements\Tables\ReimbursementsTable;
use App\Models\Reimbursement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReimbursementResource extends Resource
{
    protected static ?string $model = Reimbursement::class;

    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Reimburse';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Schema $schema): Schema
    {
        return ReimbursementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReimbursementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReimbursements::route('/'),
            'create' => CreateReimbursement::route('/create'),
            'edit' => EditReimbursement::route('/{record}/edit'),
        ];
    }
}
