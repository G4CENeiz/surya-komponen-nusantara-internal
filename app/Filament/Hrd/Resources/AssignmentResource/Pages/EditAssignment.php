<?php

namespace App\Filament\Hrd\Resources\AssignmentResource\Pages;

use App\Filament\Hrd\Resources\AssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected Width|string|null $maxContentWidth = '7xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
