@php
    $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
@endphp

<div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead>
            <tr class="border-b border-gray-200 dark:border-white/10">
                <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">#</th>
                <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Name</th>
                <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Clock In</th>
                <th class="px-3 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $index => $entry)
                @php
                    $rank = $index + 1;
                    $isCurrentUser = $entry['user_id'] === auth()->id();
                @endphp
                <tr class="border-b border-gray-100 dark:border-white/5 {{ $isCurrentUser ? 'bg-primary-50/50 dark:bg-primary-500/5' : '' }}">
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $medals[$rank] ?? $rank }}</td>
                    <td class="px-3 py-2 whitespace-nowrap font-medium {{ $isCurrentUser ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ $entry['name'] }}
                        @if($isCurrentUser)
                            <span class="ml-1 text-[10px] text-primary-500">(you)</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $entry['clock_in_at'] }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        @if($entry['is_late'])
                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400">Late</span>
                        @else
                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400">On Time</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        No attendance records yet today
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
