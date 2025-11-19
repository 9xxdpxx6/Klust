<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {
    }

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

        $results = $this->searchService->searchAll($query);

        return response()->json($results);
    }
}
