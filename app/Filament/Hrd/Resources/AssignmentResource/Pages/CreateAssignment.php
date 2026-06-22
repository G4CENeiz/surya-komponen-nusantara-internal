<?php

namespace App\Filament\Hrd\Resources\AssignmentResource\Pages;

use App\Filament\Hrd\Resources\AssignmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected Width|string|null $maxContentWidth = '7xl';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id() ?? 1;

        return $data;
    }
}
