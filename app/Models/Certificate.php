<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'icon',
        'rarity',
    ];

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(CaseModel::class, 'case_certificates')
            ->using(CaseCertificate::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_certificates')
            ->using(UserCertificate::class)
            ->withPivot('case_id')
            ->withTimestamps();
    }
}
