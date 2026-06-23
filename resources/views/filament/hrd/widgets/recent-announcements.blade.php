<div>
    <x-filament-widgets::widget>
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between">
                    <span>Pengumuman Terbaru</span>
                    <a href="{{ \App\Filament\Hrd\Resources\AnnouncementResource::getUrl('index') }}"
                       class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                        Lihat semua →
                    </a>
                </div>
            </x-slot>

            @php $announcements = $this->getAnnouncements(); @endphp

            @if($announcements->isEmpty())
                <p class="text-center text-xs text-gray-400 dark:text-gray-500 py-4">
                    Belum ada pengumuman
                </p>
            @else
                <div class="space-y-3">
                    @foreach($announcements as $announcement)
                        <div class="flex items-start gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                            <div class="mt-0.5 rounded-full {{ $announcement->type === 'assignment' ? 'bg-warning-100 dark:bg-warning-900/30' : 'bg-primary-100 dark:bg-primary-900/30' }} p-1.5">
                                @if($announcement->type === 'assignment')
                                    <x-heroicon-o-document-text class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                                @else
                                    <x-heroicon-o-megaphone class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $announcement->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    oleh {{ $announcement->creator?->name ?? 'Tidak Diketahui' }}
                                    · {{ $announcement->published_at?->diffForHumans() ?? 'Draf' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $announcement->type === 'assignment'
                                    ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                    : 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' }}">
                                {{ $announcement->type === 'assignment' ? 'Penugasan' : 'Pengumuman' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </x-filament-widgets::widget>
</div>
