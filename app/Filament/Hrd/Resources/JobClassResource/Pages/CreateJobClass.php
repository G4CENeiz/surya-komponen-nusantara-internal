<?php

namespace App\Filament\Hrd\Resources\JobClassResource\Pages;

use App\Filament\Hrd\Resources\JobClassResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateJobClass extends CreateRecord
{
    protected static string $resource = JobClassResource::class;

    protected Width|string|null $maxContentWidth = '7xl';
}
