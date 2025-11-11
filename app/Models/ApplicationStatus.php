<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'color',
    ];

    /**
     * Заявки с этим статусом
     */
    public function applications(): HasMany
    {
        return $this->hasMany(CaseApplication::class, 'status_id');
    }

    /**
     * История с этим статусом как новый статус
     */
    public function historyAsNew(): HasMany
    {
        return $this->hasMany(CaseApplicationStatusHistory::class, 'new_status_id');
    }

    /**
     * История с этим статусом как старый статус
     */
    public function historyAsOld(): HasMany
    {
        return $this->hasMany(CaseApplicationStatusHistory::class, 'old_status_id');
    }

    /**
     * Получить статус по имени
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Получить ID статуса по имени
     */
    public static function getIdByName(string $name): ?int
    {
        return static::where('name', $name)->value('id');
    }
}
