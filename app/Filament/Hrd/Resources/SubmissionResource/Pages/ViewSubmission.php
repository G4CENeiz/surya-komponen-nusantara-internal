<?php

namespace App\Filament\Hrd\Resources\SubmissionResource\Pages;

use App\Filament\Hrd\Resources\SubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    protected Width|string|null $maxContentWidth = '4xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
