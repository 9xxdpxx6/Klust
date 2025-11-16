<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'required_points',
    ];

    /**
     * Get icon path attribute
     * Returns storage path if icon is a file, null if it's a PrimeIcon class or old Font Awesome icon
     */
    protected function iconPath(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $icon = $attributes['icon'] ?? null;
                
                if (empty($icon)) {
                    return null;
                }

                // If icon starts with 'pi-' or 'fa-', it's an icon class, not a file path
                if (str_starts_with($icon, 'pi-') || str_starts_with($icon, 'fa-')) {
                    return null;
                }

                // Otherwise, it's a file path in storage
                return '/storage/' . $icon;
            }
        );
    }

    /**
     * Check if icon is a PrimeIcon class
     */
    public function isPrimeIcon(): bool
    {
        return $this->icon && (str_starts_with($this->icon, 'pi-') || str_starts_with($this->icon, 'fa-'));
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->using(UserBadge::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}

