<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function activeStudents(): HasMany
    {
        return $this->studentProfiles()->whereHas('user', function ($query) {
            $query->where('is_active', true);
        });
    }
}
