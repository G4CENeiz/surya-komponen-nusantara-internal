@props(['records'])

<div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="w-full text-left divide-y table-auto fi-ta-table">
        <thead class="bg-gray-50 text-sm font-medium text-gray-600">
            <tr>
                <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Date</th>
                <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Clock In</th>
                <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Clock Out</th>
                <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Hours</th>
                <th class="px-4 py-3 font-semibold uppercase tracking-wider text-xs">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-900">
            @forelse($records as $record)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3 font-medium">{{ $record['date'] }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $record['clock_in_at'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $record['clock_out_at'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $record['worked_hours'] ? number_format($record['worked_hours'], 1) . 'h' : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'pending_hr' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            ];
                            $color = $statusColors[$record['status']] ?? 'gray';
                            $label = ucfirst(str_replace('_', ' ', $record['status']));
                        @endphp
                        <x-filament::badge :color="$color">
                            {{ $label }}
                        </x-filament::badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <x-heroicon-o-clock class="w-8 h-8 text-gray-300 mb-2" />
                            <p>No attendance records found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
