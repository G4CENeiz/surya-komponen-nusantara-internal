<?php

namespace App\Filament\Hrd\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class HrdDashboard extends Dashboard
{
    protected static string $routePath = '/';

    use HasFiltersForm;

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public function getTitle(): string
    {
        return 'HRD Dashboard';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('dateFrom')
                    ->label('From')
                    ->native(false)
                    ->live(),
                DatePicker::make('dateTo')
                    ->label('To')
                    ->native(false)
                    ->live(),
            ])
            ->columns(2);
    }
}
