<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty',
        'group',
        'specialization',
        'bio',
        'total_points',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

