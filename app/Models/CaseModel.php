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
        'user_id',
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

    protected $appends = [
        'required_skills',
        'team_size',
        'partner',
    ];

    /**
     * Accessor для required_skills (алиас для skills)
     * Используется в Vue компонентах
     */
    public function getRequiredSkillsAttribute()
    {
        return $this->skills;
    }

    /**
     * Accessor для team_size (алиас для required_team_size)
     * Используется в Vue компонентах
     */
    public function getTeamSizeAttribute()
    {
        return $this->required_team_size;
    }

    public function partnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship to PartnerProfile through partnerUser
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(PartnerProfile::class, 'user_id', 'user_id');
    }

    /**
     * Accessor for backward compatibility with Vue components
     * Returns PartnerProfile if available through partnerUser relationship
     */
    public function getPartnerAttribute()
    {
        // Сначала проверяем, загружено ли отношение partner напрямую
        if ($this->relationLoaded('partner')) {
            $partner = $this->getRelationValue('partner');
            if ($partner) {
                return $partner;
            }
        }
        
        // Если partner не загружено, проверяем partnerUser и его partnerProfile
        // Используем прямой доступ к relation, если он загружен
        if ($this->relationLoaded('partnerUser')) {
            $partnerUser = $this->getRelationValue('partnerUser');
            if ($partnerUser && $partnerUser->relationLoaded('partnerProfile')) {
                return $partnerUser->getRelationValue('partnerProfile');
            }
            // Если partnerProfile не загружен, но partnerUser есть, загружаем его
            if ($partnerUser) {
                return $partnerUser->partnerProfile;
            }
        }
        
        // Если relations не загружены, но есть user_id, пытаемся загрузить через отношение
        // Это может вызвать N+1 проблему, но работает как fallback
        if (!$this->relationLoaded('partnerUser') && $this->user_id) {
            $partnerUser = $this->partnerUser;
            if ($partnerUser) {
                return $partnerUser->partnerProfile;
            }
        }
        
        return null;
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
    public function scopeByPartner($query, $userId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
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
