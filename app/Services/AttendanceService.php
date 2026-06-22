<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use App\Models\Workplace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private readonly GeolocationService $geoService,
    ) {}

    /**
     * Clock in an employee.
     *
     * @return array{success: bool, message: string, attendance?: Attendance}
     */
    public function clockIn(
        User $user,
        float $lat,
        float $lng,
        Request $request,
        ?float $gpsAccuracy = null,
        ?float $gpsSpeed = null,
    ): array {
        $today = now()->toDateString();

        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->clock_in_at) {
            return [
                'success' => false,
                'message' => 'You have already clocked in today.',
            ];
        }

        $workplace = $this->findNearestWorkplace($lat, $lng);

        // Validate geofence
        $withinGeofence = false;
        $distanceToWorkplace = null;

        if ($workplace) {
            $withinGeofence = $this->geoService->isWithinWorkplace($lat, $lng, $workplace);
            $distanceToWorkplace = $this->geoService->distanceToWorkplace($lat, $lng, $workplace);
        }

        // Detect GPS spoofing
        $spoofingIndicators = $this->detectSpoofing($user, $lat, $lng);

        // Determine clock-in method
        $method = $withinGeofence ? 'geofence' : 'manual';

        // Server-side timestamp (never trust client)
        $serverNow = Carbon::now();

        // Check if late — use employee shift time
        $isLate = false;
        $employee = $user->employee;
        if ($employee?->shift_start_time) {
            $shiftStart = Carbon::parse($employee->shift_start_time);
            $isLate = $serverNow->format('H:i:s') > $shiftStart->format('H:i:s');
        }

        $attendance = DB::transaction(function () use (
            $user, $workplace, $lat, $lng, $request, $withinGeofence,
            $method, $serverNow, $isLate, $today, $spoofingIndicators,
            $gpsAccuracy, $gpsSpeed, $distanceToWorkplace
        ) {
            $attendance = Attendance::updateOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                [
                    'workplace_id' => $workplace?->id,
                    'clock_in_at' => $serverNow,
                    'clock_in_lat' => $lat,
                    'clock_in_lng' => $lng,
                    'clock_in_ip' => $request->ip(),
                    'clock_in_within_geofence' => $withinGeofence,
                    'clock_in_method' => $method,
                    'is_late' => $isLate,
                    'status' => $withinGeofence
                        ? AttendanceStatus::PendingHr
                        : AttendanceStatus::PendingHr, // Always pending HR, but flagged if outside geofence
                ],
            );

            // Log the clock-in
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'action' => 'clock_in',
                'new_value' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'within_geofence' => $withinGeofence,
                    'method' => $method,
                    'is_late' => $isLate,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => array_merge([
                    'gps_accuracy' => $gpsAccuracy,
                    'gps_speed' => $gpsSpeed,
                    'distance_to_workplace_meters' => $distanceToWorkplace,
                    'server_timestamp' => $serverNow->toIso8601String(),
                    'spoofing_indicators' => $spoofingIndicators,
                ], $this->buildFingerprint($request)),
            ]);

            return $attendance;
        });

        $message = $withinGeofence
            ? 'Clock in successful. Awaiting HR verification.'
            : 'Clock in recorded but you are outside the workplace geofence. This will be flagged for HR review.';

        if (! empty($spoofingIndicators)) {
            $message .= ' ⚠️ GPS anomaly detected.';
        }

        return [
            'success' => true,
            'message' => $message,
            'attendance' => $attendance,
        ];
    }

    /**
     * Clock out an employee.
     *
     * @return array{success: bool, message: string, attendance?: Attendance}
     */
    public function clockOut(
        User $user,
        float $lat,
        float $lng,
        Request $request,
        ?float $gpsAccuracy = null,
        ?float $gpsSpeed = null,
    ): array {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->clock_in_at) {
            return [
                'success' => false,
                'message' => 'You have not clocked in today.',
            ];
        }

        if ($attendance->clock_out_at) {
            return [
                'success' => false,
                'message' => 'You have already clocked out today.',
            ];
        }

        $workplace = $attendance->workplace;

        // Validate geofence
        $withinGeofence = false;
        $distanceToWorkplace = null;

        if ($workplace) {
            $withinGeofence = $this->geoService->isWithinWorkplace($lat, $lng, $workplace);
            $distanceToWorkplace = $this->geoService->distanceToWorkplace($lat, $lng, $workplace);
        }

        // Detect GPS spoofing
        $spoofingIndicators = $this->detectSpoofing($user, $lat, $lng);

        $method = $withinGeofence ? 'geofence' : 'manual';

        // Server-side timestamp
        $serverNow = Carbon::now();

        // Check if early leave — use employee shift time
        $isEarlyLeave = false;
        $employee = $user->employee;
        if ($employee?->shift_end_time) {
            $shiftEnd = Carbon::parse($employee->shift_end_time);
            $isEarlyLeave = $serverNow->format('H:i:s') < $shiftEnd->format('H:i:s');
        }

        // Calculate worked hours
        $clockIn = Carbon::parse($attendance->clock_in_at);
        $workedHours = $clockIn->diffInMinutes($serverNow) / 60;

        DB::transaction(function () use (
            $attendance, $lat, $lng, $request, $withinGeofence,
            $method, $serverNow, $isEarlyLeave, $workedHours,
            $spoofingIndicators, $gpsAccuracy, $gpsSpeed, $distanceToWorkplace
        ) {
            $attendance->update([
                'clock_out_at' => $serverNow,
                'clock_out_lat' => $lat,
                'clock_out_lng' => $lng,
                'clock_out_ip' => $request->ip(),
                'clock_out_within_geofence' => $withinGeofence,
                'clock_out_method' => $method,
                'is_early_leave' => $isEarlyLeave,
                'worked_hours' => round($workedHours, 2),
            ]);

            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'action' => 'clock_out',
                'new_value' => [
                    'lat' => $lat,
                    'lng' => $lng,
                    'within_geofence' => $withinGeofence,
                    'method' => $method,
                    'is_early_leave' => $isEarlyLeave,
                    'worked_hours' => round($workedHours, 2),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => array_merge([
                    'gps_accuracy' => $gpsAccuracy,
                    'gps_speed' => $gpsSpeed,
                    'distance_to_workplace_meters' => $distanceToWorkplace,
                    'server_timestamp' => $serverNow->toIso8601String(),
                    'spoofing_indicators' => $spoofingIndicators,
                ], $this->buildFingerprint($request)),
            ]);

            $attendance->refresh();
        });

        $message = 'Clock out successful. Awaiting HR verification.';

        if (! $withinGeofence) {
            $message = 'Clock out recorded but you are outside the workplace geofence. Flagged for HR review.';
        }

        return [
            'success' => true,
            'message' => $message,
            'attendance' => $attendance->refresh(),
        ];
    }

    /**
     * Get the current attendance status for a user today.
     */
    public function getTodayStatus(User $user): ?Attendance
    {
        return Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();
    }

    /**
     * Build a device fingerprint from the request for spoofing detection.
     */
    private function buildFingerprint(Request $request): array
    {
        return [
            'device_fingerprint' => md5(
                $request->userAgent().
                ($request->header('Accept-Language') ?? '')
            ),
            'accept_language' => $request->header('Accept-Language'),
            'sec_ch_ua' => $request->header('Sec-CH-UA'),
            'sec_ch_ua_mobile' => $request->header('Sec-CH-UA-Mobile'),
            'sec_ch_ua_platform' => $request->header('Sec-CH-UA-Platform'),
        ];
    }

    /**
     * Detect GPS spoofing by checking recent location history.
     */
    private function detectSpoofing(User $user, float $lat, float $lng): array
    {
        $recentLogs = AttendanceLog::whereHas('attendance', fn ($q) => $q->where('user_id', $user->id))
            ->where('action', 'clock_in')
            ->where('created_at', '>=', now()->subHours(24))
            ->get()
            ->map(fn ($log) => [
                'lat' => $log->new_value['lat'] ?? null,
                'lng' => $log->new_value['lng'] ?? null,
                'timestamp' => $log->created_at,
            ])
            ->filter(fn ($loc) => $loc['lat'] !== null)
            ->values()
            ->toArray();

        return $this->geoService->detectGpsSpoofing($recentLogs, $lat, $lng);
    }

    private function findNearestWorkplace(float $lat, float $lng): ?Workplace
    {
        $workplaces = Workplace::all();

        if ($workplaces->isEmpty()) {
            return null;
        }

        return $workplaces->sortBy(function ($workplace) use ($lat, $lng) {
            return $this->geoService->distanceInMeters($lat, $lng, $workplace->latitude, $workplace->longitude);
        })->first();
    }
}
