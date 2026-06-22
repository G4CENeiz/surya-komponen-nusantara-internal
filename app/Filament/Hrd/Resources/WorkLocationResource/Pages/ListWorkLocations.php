<?php

namespace App\Filament\Hrd\Resources\WorkLocationResource\Pages;

use App\Filament\Hrd\Resources\WorkLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkLocations extends ListRecords
{
    protected static string $resource = WorkLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
