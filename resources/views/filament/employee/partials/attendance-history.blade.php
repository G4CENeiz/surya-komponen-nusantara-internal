@props(['records'])

<div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
        <thead>
            <tr style="border-bottom: 1px solid rgba(128, 128, 128, 0.2);">
                <th style="padding: 0.625rem 0.75rem; text-align: left; font-weight: 600; opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Date</th>
                <th style="padding: 0.625rem 0.75rem; text-align: left; font-weight: 600; opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Clock In</th>
                <th style="padding: 0.625rem 0.75rem; text-align: left; font-weight: 600; opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Clock Out</th>
                <th style="padding: 0.625rem 0.75rem; text-align: left; font-weight: 600; opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Hours</th>
                <th style="padding: 0.625rem 0.75rem; text-align: left; font-weight: 600; opacity: 0.6; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr style="border-bottom: 1px solid rgba(128, 128, 128, 0.1);">
                    <td style="padding: 0.625rem 0.75rem; font-weight: 500;">{{ $record['date'] }}</td>
                    <td style="padding: 0.625rem 0.75rem;">{{ $record['clock_in_at'] ?? '—' }}</td>
                    <td style="padding: 0.625rem 0.75rem;">{{ $record['clock_out_at'] ?? '—' }}</td>
                    <td style="padding: 0.625rem 0.75rem;">
                        {{ $record['worked_hours'] ? number_format($record['worked_hours'], 1) . 'h' : '—' }}
                    </td>
                    <td style="padding: 0.625rem 0.75rem;">
                        @php
                            $statusStyles = [
                                'pending_hr' => ['bg' => 'rgba(245, 158, 11, 0.15)', 'text' => '#f59e0b'],
                                'approved' => ['bg' => 'rgba(34, 197, 94, 0.15)', 'text' => '#22c55e'],
                                'rejected' => ['bg' => 'rgba(239, 68, 68, 0.15)', 'text' => '#ef4444'],
                            ];
                            $style = $statusStyles[$record['status']] ?? ['bg' => 'rgba(107, 114, 128, 0.15)', 'text' => '#6b7280'];
                            $label = ucfirst(str_replace('_', ' ', $record['status']));
                        @endphp
                        <span style="display: inline-flex; align-items: center; border-radius: 9999px; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; background-color: {{ $style['bg'] }}; color: {{ $style['text'] }};">
                            {{ $label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; opacity: 0.6;">
                        No attendance records found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
