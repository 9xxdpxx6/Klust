<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Partner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        // Получаем параметры фильтрации из запроса
        $filters = [
            'search' => $request->input('search', ''),
            'status' => $request->input('status', ''),
            'partner_id' => $request->input('partner_id', ''),
            'team_size' => $request->input('team_size', ''),
            'perPage' => $request->input('perPage', 15),
        ];

        // Строим запрос
        $query = CaseModel::with(['partner.user'])
            ->withCount('applications');

        // Поиск по названию или описанию
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Фильтрация по статусу
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Фильтрация по партнеру
        if (!empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }

        // Фильтрация по размеру команды
        if (!empty($filters['team_size'])) {
            $teamSize = (int)$filters['team_size'];
            if ($teamSize === 5) {
                $query->where('required_team_size', '>=', 5);
            } else {
                $query->where('required_team_size', $teamSize);
            }
        }

        // Сортировка и пагинация
        $cases = $query->orderBy('created_at', 'desc')
            ->paginate($filters['perPage'])
            ->withQueryString();

        // Получаем список партнеров для фильтра
        $partners = Partner::with('user')
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'company_name' => $partner->company_name,
                    'contact_person' => $partner->user->name,
                ];
            });

        return Inertia::render('Admin/Cases/Index', [
            'cases' => $cases,
            'filters' => $filters,
            'partners' => $partners,
        ]);
    }
}
