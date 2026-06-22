<?php

namespace App\Filament\Hrd\Resources\UserResource\Pages;

use App\Filament\Hrd\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
