<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CaseTeamMember extends Pivot
{
    protected $table = 'case_team_members';

    protected $fillable = [
        'application_id',
        'user_id',
    ];
}

