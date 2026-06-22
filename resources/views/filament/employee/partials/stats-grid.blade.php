@php
    $statusColors = [
        'pending_hr' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#f59e0b'],
        'approved'   => ['bg' => 'rgba(34, 197, 94, 0.15)',  'text' => '#22c55e'],
        'rejected'   => ['bg' => 'rgba(239, 68, 68, 0.15)',  'text' => '#ef4444'],
    ];

    $geoColors = [
        true  => ['bg' => 'rgba(34, 197, 94, 0.15)',  'text' => '#22c55e'],
        false => ['bg' => 'rgba(239, 68, 68, 0.15)',  'text' => '#ef4444'],
        null  => ['bg' => 'rgba(107, 114, 128, 0.15)', 'text' => '#6b7280'],
    ];

    $geoText = match ($geofence) {
        true  => 'Inside',
        false => 'Outside',
        default => '—',
    };

    $statusText = $status ? ucfirst(str_replace('_', ' ', $status)) : '—';
    $statusStyle = $statusColors[$status] ?? ['bg' => 'rgba(107, 114, 128, 0.15)', 'text' => '#6b7280'];
    $geoStyle = $geoColors[$geofence] ?? $geoColors[null];

    $clockInDisplay = $isLate ? 'Late · ' . $clockIn : $clockIn;
    $clockInBadge = $isLate
        ? ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#ef4444']
        : null;
@endphp

<div class="grid grid-cols-2 gap-x-8 gap-y-5">
    {{-- Row 1: Clock In | Clock Out --}}
    <div>
        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Clock In</div>
        @if($isLate)
            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">Late · {{ $clockIn }}</span>
        @else
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $clockIn }}</div>
        @endif
    </div>

    <div>
        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Clock Out</div>
        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $clockOut }}</div>
    </div>

    {{-- Row 2: Geofence | Status --}}
    <div>
        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Geofence</div>
        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
              style="background-color: {{ $geoStyle['bg'] }}; color: {{ $geoStyle['text'] }};">
            {{ $geoText }}
        </span>
    </div>

    <div>
        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Status</div>
        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
              style="background-color: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['text'] }};">
            {{ $statusText }}
        </span>
    </div>

    {{-- Row 3: Hours --}}
    <div>
        <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Hours</div>
        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $hours ?? '—' }}{{ $hours ? 'h' : '' }}</div>
    </div>
</div>
