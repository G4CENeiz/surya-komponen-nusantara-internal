<?php

namespace App\Filament\Hrd\Resources\WorkLocationResource\Pages;

use App\Filament\Hrd\Resources\WorkLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditWorkLocation extends EditRecord
{
    protected static string $resource = WorkLocationResource::class;

    protected Width|string|null $maxContentWidth = '4xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
