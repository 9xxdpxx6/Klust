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
            'user_id' => $data['user_id'] ?? $data['partner_id'] ?? null,
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
            'user_id' => $data['user_id'] ?? $data['partner_id'] ?? $simulator->user_id,
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
     * Get student sessions
     */
    public function getStudentSessions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return SimulatorSession::with('simulator')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Complete simulator session
     */
    public function completeSession(SimulatorSession $session, array $data): SimulatorSession
    {
        if ($session->completed_at !== null) {
            throw new \Exception('Session is already completed');
        }

        return DB::transaction(function () use ($session, $data) {
            // Update session
            $session->update([
                'score' => $data['score'] ?? 0,
                'time_spent' => $data['time_spent'] ?? null,
                'answers' => $data['answers'] ?? null,
                'completed_at' => now(),
            ]);

            // Award points and update student progress
            // This will be handled by ProgressLogService

            return $session->fresh();
        });
    }
}
