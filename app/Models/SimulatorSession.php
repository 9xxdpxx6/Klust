<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulatorSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'simulator_id',
        'state',
        'score',
        'time_spent',
        'is_completed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'state' => 'array',
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function simulator(): BelongsTo
    {
        return $this->belongsTo(Simulator::class);
    }
}
