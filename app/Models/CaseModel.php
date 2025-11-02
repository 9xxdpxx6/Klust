<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\CaseModelFactory;

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
        return $this->belongsToMany(Skill::class, 'case_skills')
            ->withTimestamps();
    }
}

