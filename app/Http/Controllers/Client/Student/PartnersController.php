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
        // Убираем middleware auth, чтобы маршрут был доступен для всех
        // Проверка авторизации будет внутри метода show
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

        // Определяем, какой шаблон использовать в зависимости от авторизации
        $isAuthenticated = auth()->check();
        $isStudent = $isAuthenticated && auth()->user()->hasRole('student');

        // Если пользователь авторизован как студент, используем студенческий шаблон
        // Иначе используем гостевой шаблон
        $template = $isStudent ? 'Client/Student/Partners/Show' : 'Guest/Partners/Show';

        // Получить активные кейсы партнера для гостей
        $cases = [];
        if (!$isStudent) {
            $cases = \App\Models\CaseModel::where('user_id', $partner->id)
                ->where('status', 'active')
                ->with(['skills'])
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        }

        return Inertia::render($template, [
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
            'cases' => $cases,
        ]);
    }
}


