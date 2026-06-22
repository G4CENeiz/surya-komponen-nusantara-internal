<?php

namespace App\Filament\Hrd\Resources\EmployeeResource\Pages;

use App\Filament\Hrd\Resources\EmployeeResource;
use Filament\Pages\BasePage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected Width|string|null $maxContentWidth = '7xl';
}
