<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserCertificate extends Pivot
{
    protected $table = 'user_certificates';

    protected $fillable = [
        'user_id',
        'certificate_id',
        'case_id',
    ];

    public $incrementing = true;
}
