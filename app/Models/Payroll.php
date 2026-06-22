<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_period_id',
        'base_salary',
        'allowance',
        'overtime_hours',
        'overtime_pay',
        'bpjs_health',
        'bpjs_employment',
        'pph21',
        'tardiness_deduction',
        'other_deductions',
        'take_home_pay',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'bpjs_health' => 'decimal:2',
            'bpjs_employment' => 'decimal:2',
            'pph21' => 'decimal:2',
            'tardiness_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'take_home_pay' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
