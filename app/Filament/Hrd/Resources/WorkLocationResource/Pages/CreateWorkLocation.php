<?php

namespace App\Filament\Hrd\Resources\WorkLocationResource\Pages;

use App\Filament\Hrd\Resources\WorkLocationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateWorkLocation extends CreateRecord
{
    protected static string $resource = WorkLocationResource::class;

    protected Width|string|null $maxContentWidth = '4xl';
}
