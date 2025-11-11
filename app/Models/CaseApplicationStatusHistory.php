<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseApplicationStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'case_application_status_history';

    protected $fillable = [
        'case_application_id',
        'old_status_id',
        'new_status_id',
        'comment',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Заявка на кейс
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(CaseApplication::class, 'case_application_id');
    }

    /**
     * Пользователь, который изменил статус
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Старый статус
     */
    public function oldStatus(): BelongsTo
    {
        return $this->belongsTo(ApplicationStatus::class, 'old_status_id');
    }

    /**
     * Новый статус
     */
    public function newStatus(): BelongsTo
    {
        return $this->belongsTo(ApplicationStatus::class, 'new_status_id');
    }
}
