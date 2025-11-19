<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CaseTeamMember extends Pivot
{
    protected $table = 'case_team_members';

    protected $fillable = [
        'application_id',
        'user_id',
    ];

    /**
     * Связь с пользователем (участником команды)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с заявкой на кейс
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CaseApplication::class, 'application_id');
    }
}
