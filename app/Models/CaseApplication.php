<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'leader_id',
        'motivation',
        'status_id',
        'rejection_reason',
        'partner_comment',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ApplicationStatus::class, 'status_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(CaseTeamMember::class, 'application_id');
    }

    // Получить всю команду (через pivot)
    public function team()
    {
        return $this->belongsToMany(User::class, 'case_team_members')
            ->using(CaseTeamMember::class)
            ->withTimestamps();
    }

    /**
     * История изменений статуса заявки
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(CaseApplicationStatusHistory::class, 'case_application_id')
            ->orderBy('changed_at', 'desc');
    }

    /**
     * Scope: фильтр по названию статуса через отношение
     */
    public function scopeWithStatus($query, string $statusName)
    {
        return $query->whereHas('status', function ($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }

    /**
     * Scope: только ожидающие заявки
     */
    public function scopePending($query)
    {
        return $query->withStatus('pending');
    }

    /**
     * Scope: только принятые заявки
     */
    public function scopeAccepted($query)
    {
        return $query->withStatus('accepted');
    }

    /**
     * Scope: только отклоненные заявки
     */
    public function scopeRejected($query)
    {
        return $query->withStatus('rejected');
    }

    /**
     * Scope: только завершенные заявки
     */
    public function scopeCompleted($query)
    {
        return $query->withStatus('completed');
    }
}

