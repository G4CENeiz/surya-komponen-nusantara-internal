<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
        'min_salary',
        'max_salary',
        'base_allowance',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'min_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
            'base_allowance' => 'decimal:2',

        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
