<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Simulator extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'title',
        'slug',
        'description',
        'preview_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SimulatorSession::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseModel::class);
    }
}

