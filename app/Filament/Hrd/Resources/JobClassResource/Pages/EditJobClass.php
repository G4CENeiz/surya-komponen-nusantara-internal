<?php

namespace App\Filament\Hrd\Resources\JobClassResource\Pages;

use App\Filament\Hrd\Resources\JobClassResource;
use Filament\Actions;
use Filament\Pages\BasePage;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditJobClass extends EditRecord
{
    protected static string $resource = JobClassResource::class;

    protected Width|string|null $maxContentWidth = '7xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
