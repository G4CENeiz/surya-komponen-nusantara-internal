<?php

namespace App\Filament\Hrd\Resources\AnnouncementResource\Pages;

use App\Filament\Hrd\Resources\AnnouncementResource;
use Filament\Pages\BasePage;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected Width|string|null $maxContentWidth = '7xl';
}
