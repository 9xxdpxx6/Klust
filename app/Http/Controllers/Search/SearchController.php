<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return response()->json([
                'cases' => [],
                'users' => [],
                'skills' => [],
            ]);
        }

        // Search cases by title and description
        $cases = CaseModel::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with(['partner', 'required_skills'])
            ->limit(10)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'title' => $case->title,
                    'description' => str_limit(strip_tags($case->description), 100),
                    'partner' => $case->partner->company_name ?? null,
                    'deadline' => $case->deadline->format('d.m.Y'),
                    'type' => 'case',
                    'url' => route('student.cases.show', $case->id),
                ];
            });

        // Search users by name and email
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => 'user',
                    'url' => '#', // Add appropriate route when needed
                ];
            });

        // Search skills by name
        $skills = Skill::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'description' => $skill->description,
                    'type' => 'skill',
                    'url' => '#', // Add appropriate route when needed
                ];
            });

        return response()->json([
            'cases' => $cases,
            'users' => $users,
            'skills' => $skills,
        ]);
    }
}