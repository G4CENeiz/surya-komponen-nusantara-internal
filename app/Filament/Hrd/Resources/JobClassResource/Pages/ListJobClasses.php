<?php

namespace App\Filament\Hrd\Resources\JobClassResource\Pages;

use App\Filament\Hrd\Resources\JobClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobClasses extends ListRecords
{
    protected static string $resource = JobClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
