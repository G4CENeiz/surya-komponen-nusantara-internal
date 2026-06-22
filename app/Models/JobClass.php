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
        'base_salary',
        'allowance',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
