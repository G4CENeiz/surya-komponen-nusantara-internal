<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'office_email',
        'department_id',
        'job_class_id',
        'work_location_id',
        'hire_date',
        'termination_date',
        'status',
        'face_photo_path',
        'base_salary',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobClass(): BelongsTo
    {
        return $this->belongsTo(JobClass::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function announcements(): BelongsTo
    {
        return $this->belongsToMany(Announcement::class, 'employee_announcements')
            ->withPivot(['is_read', 'read_at'])
            ->withTimestamps();
    }

    // ── Scopes ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
