@props(['records'])

<div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead>
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 500; color: #6b7280;">Date</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 500; color: #6b7280;">Clock In</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 500; color: #6b7280;">Clock Out</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 500; color: #6b7280;">Hours</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 500; color: #6b7280;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 0.75rem 1rem; color: #111827;">{{ $record['date'] }}</td>
                    <td style="padding: 0.75rem 1rem; color: #111827;">{{ $record['clock_in_at'] ?? '—' }}</td>
                    <td style="padding: 0.75rem 1rem; color: #111827;">{{ $record['clock_out_at'] ?? '—' }}</td>
                    <td style="padding: 0.75rem 1rem; color: #111827;">
                        {{ $record['worked_hours'] ? number_format($record['worked_hours'], 1) . 'h' : '—' }}
                    </td>
                    <td style="padding: 0.75rem 1rem;">
                        @php
                            $statusColors = [
                                'pending_hr' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                'approved' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                            ];
                            $colors = $statusColors[$record['status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                            $label = ucfirst(str_replace('_', ' ', $record['status']));
                        @endphp
                        <span style="display: inline-flex; align-items: center; border-radius: 0.375rem; padding: 0.125rem 0.5rem; font-size: 0.75rem; font-weight: 500; background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
                            {{ $label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 1.5rem; text-align: center; color: #6b7280;">
                        No attendance records found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
