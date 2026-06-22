<?php

namespace App\Filament\Hrd\Resources\AttendanceResource\Pages;

use App\Filament\Hrd\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    protected Width|string|null $maxContentWidth = '4xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
