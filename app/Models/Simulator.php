<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = [
        'preview_image_url',
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

    protected function previewImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->preview_image
                ? Storage::disk('public')->url($this->preview_image)
                : null,
        );
    }
}
