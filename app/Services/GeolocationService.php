<?php

namespace App\Services;

use App\Models\Workplace;

class GeolocationService
{
    /**
     * Earth's radius in meters.
     */
    private const EARTH_RADIUS = 6_371_000;

    /**
     * Calculate distance between two GPS coordinates using the Haversine formula.
     */
    public function distanceInMeters(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
    ): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * Check if a point is within the workplace geofence.
     */
    public function isWithinWorkplace(float $lat, float $lng, Workplace $workplace): bool
    {
        $distance = $this->distanceInMeters($lat, $lng, $workplace->latitude, $workplace->longitude);

        return $distance <= $workplace->radius_meters;
    }

    /**
     * Get the distance from a point to the workplace in meters.
     */
    public function distanceToWorkplace(float $lat, float $lng, Workplace $workplace): float
    {
        return $this->distanceInMeters($lat, $lng, $workplace->latitude, $workplace->longitude);
    }

    /**
     * Detect potential GPS spoofing based on common indicators.
     *
     * Returns an array of suspicious indicators. Empty array = no issues detected.
     */
    public function detectGpsSpoofing(array $previousLocations, float $currentLat, float $currentLng): array
    {
        $indicators = [];

        // 1. Impossibly fast movement (teleportation detection)
        // If user moved more than 500m in less than 5 seconds, it's suspicious
        if (count($previousLocations) >= 2) {
            $last = end($previousLocations);
            $distance = $this->distanceInMeters(
                $last['lat'],
                $last['lng'],
                $currentLat,
                $currentLng
            );
            $timeDiff = now()->diffInSeconds($last['timestamp']);

            if ($timeDiff > 0 && $distance / $timeDiff > 100) { // > 100 m/s = 360 km/h
                $indicators[] = [
                    'type' => 'teleportation',
                    'message' => "Moved {$distance}m in {$timeDiff}s (possible GPS spoofing)",
                    'distance' => $distance,
                    'time_diff' => $timeDiff,
                ];
            }
        }

        // 2. GPS accuracy check
        // If accuracy is reported as 0 or suspiciously low (like exactly 10.0)
        // This is handled on the client side - the metadata should include accuracy

        // 3. Identical coordinates repeated (could be hardcoded fake GPS)
        if (count($previousLocations) >= 3) {
            $uniqueCoords = collect($previousLocations)
                ->map(fn ($loc) => round($loc['lat'], 5).','.round($loc['lng'], 5))
                ->unique()
                ->count();

            if ($uniqueCoords <= 1 && count($previousLocations) >= 5) {
                $indicators[] = [
                    'type' => 'static_coordinates',
                    'message' => 'GPS coordinates have not changed over multiple reports (possible static fake GPS)',
                ];
            }
        }

        return $indicators;
    }
}
