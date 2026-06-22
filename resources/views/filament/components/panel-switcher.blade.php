@php
    $currentPanel = \Filament\Filament::getCurrentPanel()->getId();
@endphp

<div class="mt-6">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-white dark:bg-gray-900 px-2 text-gray-500 dark:text-gray-400">
                {{ __('Switch Panel') }}
            </span>
        </div>
    </div>

    <div class="mt-4 flex gap-2">
        @if ($currentPanel !== 'employee')
            <a
                href="{{ route('filament.employee.auth.login') }}"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <x-heroicon-o-user-group class="h-4 w-4" />
                Employee
            </a>
        @endif

        @if ($currentPanel !== 'hrd')
            <a
                href="{{ route('filament.hrd.auth.login') }}"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <x-heroicon-o-briefcase class="h-4 w-4" />
                HRD
            </a>
        @endif

        @if ($currentPanel !== 'accounting')
            <a
                href="{{ route('filament.accounting.auth.login') }}"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <x-heroicon-o-calculator class="h-4 w-4" />
                Accounting
            </a>
        @endif
    </div>
</div>
