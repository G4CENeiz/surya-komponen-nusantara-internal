<?php

namespace App\Filament\Hrd\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RecentAnnouncementsWidget extends Widget
{
    protected string $view = 'filament.hrd.widgets.recent-announcements';

    protected static ?int $sort = 3;

    public function getAnnouncements(): Collection
    {
        return Announcement::where('is_active', true)
            ->with('creator')
            ->latest('published_at')
            ->limit(3)
            ->get();
    }
}
