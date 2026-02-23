<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SimulatorService
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Create new simulator
     */
    public function createSimulator(array $data): Simulator
    {
        $previewImagePath = null;

        // Handle preview image upload
        if (isset($data['preview_image'])) {
            $previewImagePath = $this->fileService->storeSimulatorPreview($data['preview_image']);
        }

        return Simulator::create([
            'user_id' => $data['user_id'] ?? null,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'preview_image' => $previewImagePath,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update simulator
     */
    public function updateSimulator(Simulator $simulator, array $data): Simulator
    {
        // Handle preview image update
        if (isset($data['preview_image'])) {
            // Delete old preview image if exists
            if ($simulator->preview_image) {
                $this->fileService->deleteFile($simulator->preview_image);
            }

            $simulator->preview_image = $this->fileService->storeSimulatorPreview($data['preview_image']);
        }

        $simulator->update([
            'user_id' => $data['user_id'] ?? $simulator->user_id,
            'title' => $data['title'] ?? $simulator->title,
            'slug' => $data['slug'] ?? $simulator->slug,
            'description' => $data['description'] ?? $simulator->description,
            'preview_image' => $simulator->preview_image,
            'is_active' => $data['is_active'] ?? $simulator->is_active,
        ]);

        return $simulator->fresh();
    }

    /**
     * Delete simulator
     */
    public function deleteSimulator(Simulator $simulator): bool
    {
        // Check for active sessions
        $activeSessions = $simulator->sessions()
            ->whereNull('completed_at')
            ->count();

        if ($activeSessions > 0) {
            throw new \Exception("Cannot delete simulator with {$activeSessions} active session(s)");
        }

        return DB::transaction(function () use ($simulator) {
            // Delete preview image if exists
            if ($simulator->preview_image) {
                $this->fileService->deleteFile($simulator->preview_image);
            }

            return $simulator->delete();
        });
    }

    /**
     * Start simulator session
     */
    public function startSession(User $user, Simulator $simulator): SimulatorSession
    {
        // Check for active session
        if ($this->hasActiveSession($user, $simulator)) {
            throw new \Exception('User already has an active session for this simulator');
        }

        return SimulatorSession::create([
            'user_id' => $user->id,
            'simulator_id' => $simulator->id,
            'started_at' => now(),
        ]);
    }

    /**
     * Check if user has active session
     */
    public function hasActiveSession(User $user, Simulator $simulator): bool
    {
        return SimulatorSession::where('user_id', $user->id)
            ->where('simulator_id', $simulator->id)
            ->whereNull('completed_at')
            ->exists();
    }

    /**
     * Get filtered simulators with pagination
     */
    public function getFilteredSimulators(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Simulator::with(['partnerUser.partnerProfile']);

        // Apply search filter
        $search = \App\Helpers\FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = \App\Helpers\FilterHelper::sanitizeSearch($search);
            $query->where(function ($q) use ($sanitizedSearch) {
                $q->where('title', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('slug', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('description', 'like', "%{$sanitizedSearch}%");
            });
        }

        // Apply status filter
        $status = \App\Helpers\FilterHelper::getStringFilter($filters['status'] ?? null);
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Get pagination parameters
        $pagination = \App\Helpers\FilterHelper::getPaginationParams($filters, 15);

        $paginator = $query->orderBy('title')
            ->paginate($pagination['per_page'])
            ->withQueryString();

        // Transform data to include partner contact person
        $paginator->getCollection()->transform(function ($simulator) {
            if ($simulator->partnerUser) {
                $simulator->partner = $simulator->partnerUser->partnerProfile;
            }

            return $simulator;
        });

        return $paginator;
    }

    /**
     * Get available simulators (active only)
     */
    public function getAvailableSimulators(): \Illuminate\Database\Eloquent\Collection
    {
        return Simulator::with('partnerUser.partnerProfile')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    /**
     * Get student sessions (active and completed separately)
     */
    public function getStudentSessions(User $user): array
    {
        // Исключаем поле state из выборки для оптимизации (оно может быть очень большим)
        $selectColumns = ['id', 'user_id', 'simulator_id', 'score', 'time_spent', 'points_earned', 'is_completed', 'started_at', 'completed_at', 'created_at', 'updated_at'];

        $activeSessions = SimulatorSession::with('simulator')
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->select($selectColumns)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $completedSessions = SimulatorSession::with('simulator')
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->select($selectColumns)
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'active' => $activeSessions,
            'completed' => $completedSessions,
        ];
    }

    /**
     * Complete simulator session.
     * Returns the session (fresh from DB) or null if already completed.
     */
    public function completeSession(SimulatorSession $session, array $data): SimulatorSession
    {
        if ($session->completed_at !== null) {
            return $session;
        }

        return DB::transaction(function () use ($session, $data) {
            // Re-check inside transaction to avoid race conditions
            $session->refresh();
            if ($session->completed_at !== null) {
                return $session;
            }

            // Update session
            $session->update([
                'score' => $data['score'] ?? 0,
                'time_spent' => $data['time_spent'] ?? null,
                'answers' => $data['answers'] ?? null,
                'completed_at' => now(),
            ]);

            return $session->fresh();
        });
    }

    /**
     * Обновить состояние сессии (merge с существующим)
     */
    public function updateSessionState(SimulatorSession $session, array $state): SimulatorSession
    {
        return DB::transaction(function () use ($session, $state) {
            $currentState = $session->state ?? [];

            // Глубокий merge состояний (правильный merge, который заменяет значения, а не создает массивы)
            $newState = $this->deepMerge($currentState, $state);

            $session->update([
                'state' => $newState,
            ]);

            return $session->fresh();
        });
    }

    /**
     * Глубокий merge массивов.
     * Associative arrays are merged recursively; list arrays are REPLACED entirely.
     * This ensures that sending messages: [] or score_history: [] actually clears old data.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function deepMerge(array $array1, array $array2): array
    {
        $result = $array1;

        foreach ($array2 as $key => $value) {
            if (
                isset($result[$key])
                && is_array($result[$key])
                && is_array($value)
                && !array_is_list($value)   // Only deep-merge ASSOCIATIVE arrays
            ) {
                // Рекурсивный merge ассоциативных массивов (dialogue, client, ui, ...)
                $result[$key] = $this->deepMerge($result[$key], $value);
            } else {
                // Скалярные значения и LIST-массивы (messages, score_history, selected_options)
                // заменяются целиком — это критично для корректного сброса при рестарте
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Получить состояние сессии
     */
    public function getSessionState(SimulatorSession $session): array
    {
        return $session->state ?? [];
    }
}
