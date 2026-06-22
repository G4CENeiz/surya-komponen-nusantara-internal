<x-filament-panels::page>
    {{-- Welcome Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Welcome back, {{ auth()->user()->name }}! 👋
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Here's what's happening with your HR department today.
        </p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        {{-- Total Employees --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Employees</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['total_employees']) }}</p>
                </div>
                <div class="rounded-lg bg-primary-100 p-3 dark:bg-primary-900/30">
                    <x-heroicon-o-users class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>
            </div>
            <div class="mt-3">
                <span class="inline-flex items-center text-sm text-success-600 dark:text-success-400">
                    <x-heroicon-m-arrow-trending-up class="mr-1 h-4 w-4" />
                    {{ $this->getStats()['active_employees'] }} active
                </span>
            </div>
        </div>

        {{-- Active Job Classes --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Classes</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['total_job_classes']) }}</p>
                </div>
                <div class="rounded-lg bg-warning-100 p-3 dark:bg-warning-900/30">
                    <x-heroicon-o-briefcase class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">Salary grades defined</span>
            </div>
        </div>

        {{-- Departments --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Departments</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['total_departments']) }}</p>
                </div>
                <div class="rounded-lg bg-info-100 p-3 dark:bg-info-900/30">
                    <x-heroicon-o-building-office class="h-6 w-6 text-info-600 dark:text-info-400" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">Organizational units</span>
            </div>
        </div>

        {{-- Announcements --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Announcements</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['total_announcements']) }}</p>
                </div>
                <div class="rounded-lg bg-danger-100 p-3 dark:bg-danger-900/30">
                    <x-heroicon-o-megaphone class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                </div>
            </div>
            <div class="mt-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">Active posts</span>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Quick Actions + Recent Announcements --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Quick Actions --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ \App\Filament\Hrd\Resources\EmployeeResource::getUrl('create') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
                    <div class="rounded-lg bg-primary-100 p-2 dark:bg-primary-900/30">
                        <x-heroicon-o-user-plus class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Add New Employee</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Register a new employee profile</p>
                    </div>
                </a>
                <a href="{{ \App\Filament\Hrd\Resources\JobClassResource::getUrl('create') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
                    <div class="rounded-lg bg-warning-100 p-2 dark:bg-warning-900/30">
                        <x-heroicon-o-plus-circle class="h-5 w-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Create Job Class</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Define a new salary grade level</p>
                    </div>
                </a>
                <a href="{{ \App\Filament\Hrd\Resources\AnnouncementResource::getUrl('create') }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition hover:border-primary-300 hover:bg-primary-50 dark:border-gray-700 dark:hover:border-primary-600 dark:hover:bg-primary-900/20">
                    <div class="rounded-lg bg-info-100 p-2 dark:bg-info-900/30">
                        <x-heroicon-o-megaphone class="h-5 w-5 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Post Announcement</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Share news or assignments</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent Announcements --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Announcements</h2>
                <a href="{{ \App\Filament\Hrd\Resources\AnnouncementResource::getUrl('index') }}"
                   class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400">
                    View all →
                </a>
            </div>
            @php $announcements = $this->getRecentAnnouncements(); @endphp
            @if($announcements->isEmpty())
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <x-heroicon-o-megaphone class="mb-2 h-10 w-10 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No announcements yet</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($announcements as $announcement)
                        <div class="flex items-start gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                            <div class="mt-0.5 rounded-full {{ $announcement->type === 'assignment' ? 'bg-warning-100 dark:bg-warning-900/30' : 'bg-info-100 dark:bg-info-900/30' }} p-1.5">
                                @if($announcement->type === 'assignment')
                                    <x-heroicon-o-document-text class="h-4 w-4 text-warning-600 dark:text-warning-400" />
                                @else
                                    <x-heroicon-o-megaphone class="h-4 w-4 text-info-600 dark:text-info-400" />
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $announcement->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    by {{ $announcement->creator?->name ?? 'Unknown' }}
                                    · {{ $announcement->published_at?->diffForHumans() ?? 'Draft' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $announcement->type === 'assignment'
                                    ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                    : 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400' }}">
                                {{ ucfirst($announcement->type) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
