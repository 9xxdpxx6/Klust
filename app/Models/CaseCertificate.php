<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CaseCertificate extends Pivot
{
    protected $table = 'case_certificates';

    protected $fillable = [
        'case_id',
        'certificate_id',
    ];

    public $incrementing = true;
}
