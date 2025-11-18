<?php

namespace App\Models;

use Database\Factories\CaseModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseModel extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return CaseModelFactory::new();
    }

    protected $table = 'cases';

    protected $fillable = [
        'partner_id',
        'title',
        'description',
        'simulator_id',
        'deadline',
        'reward',
        'required_team_size',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function simulator(): BelongsTo
    {
        return $this->belongsTo(Simulator::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CaseApplication::class, 'case_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'case_skills', 'case_id', 'skill_id');
    }

    /**
     * Relationship with case_skills intermediate model
     */

    // Добавить в класс CaseModel

    /**
     * Scope для фильтрации по статусу
     */
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Scope для поиска
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Scope для партнера
     */
    public function scopeByPartner($query, $partnerId)
    {
        if ($partnerId) {
            return $query->where('partner_id', $partnerId);
        }

        return $query;
    }

    /**
     * Проверяет, активен ли кейс
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->deadline->isFuture();
    }

    /**
     * Проверяет, просрочен ли кейс
     */
    public function isOverdue(): bool
    {
        return $this->deadline->isPast();
    }
}
