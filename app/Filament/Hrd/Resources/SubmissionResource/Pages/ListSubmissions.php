<?php

namespace App\Filament\Hrd\Resources\SubmissionResource\Pages;

use App\Filament\Hrd\Resources\SubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListSubmissions extends ListRecords
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
