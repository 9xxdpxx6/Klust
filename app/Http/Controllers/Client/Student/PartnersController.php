<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PartnersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:student']);
    }

    public function show(User $partner): Response
    {
        if (!$partner->hasRole('partner')) {
            abort(404);
        }

        $partner->load(['partnerProfile']);

        if (!$partner->partnerProfile) {
            abort(404);
        }

        return Inertia::render('Client/Student/Partners/Show', [
            'partnerUser' => [
                'id' => $partner->id,
                'name' => $partner->name,
            ],
            'partnerProfile' => [
                'company_name' => $partner->partnerProfile->company_name,
                'inn' => $partner->partnerProfile->inn,
                'address' => $partner->partnerProfile->address,
                'website' => $partner->partnerProfile->website,
                'description' => $partner->partnerProfile->description,
                'contact_person' => $partner->partnerProfile->contact_person,
                'contact_phone' => $partner->partnerProfile->contact_phone,
                'logo' => $partner->partnerProfile->logo,
            ],
        ]);
    }
}


