<?php

namespace App\Filament\Hrd\Resources\JobClassResource\Pages;

use App\Filament\Hrd\Resources\JobClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobClass extends EditRecord
{
    protected static string $resource = JobClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
