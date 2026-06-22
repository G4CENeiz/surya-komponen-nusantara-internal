<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'office_id',
        'date',
        'clock_in_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_in_ip',
        'clock_in_photo_path',
        'clock_in_face_confidence',
        'clock_in_within_geofence',
        'clock_in_method',
        'clock_out_at',
        'clock_out_lat',
        'clock_out_lng',
        'clock_out_ip',
        'clock_out_photo_path',
        'clock_out_face_confidence',
        'clock_out_within_geofence',
        'clock_out_method',
        'status',
        'hr_notes',
        'verified_by',
        'verified_at',
        'worked_hours',
        'is_late',
        'is_early_leave',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'clock_in_within_geofence' => 'boolean',
            'clock_out_within_geofence' => 'boolean',
            'status' => AttendanceStatus::class,
            'verified_at' => 'datetime',
            'is_late' => 'boolean',
            'is_early_leave' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    /**
     * Scope to get today's attendance.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    /**
     * Scope to get pending HR review.
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', AttendanceStatus::PendingHr);
    }

    /**
     * Scope to get attendance outside geofence.
     */
    public function scopeOutsideGeofence($query)
    {
        return $query->where('clock_in_within_geofence', false)
            ->orWhere('clock_out_within_geofence', false);
    }
}
