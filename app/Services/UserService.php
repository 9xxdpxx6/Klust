<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Create a new user with profile
     *
     * @param  array  $data  User data including role
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'kubgtu_id' => $data['kubgtu_id'] ?? null,
            ]);

            // Create appropriate profile based on role
            $role = $data['role'] ?? 'student';
            $this->createProfileForUser($user, $role, $data);

            // Assign role via Spatie Permission
            $user->assignRole($role);

            // Handle avatar upload if present
            if (isset($data['avatar'])) {
                $avatarPath = $this->fileService->storeAvatar($data['avatar']);
                $user->update(['avatar' => $avatarPath]);
            }

            return $user->fresh();
        });
    }

    /**
     * Update existing user
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user basic info
            $userUpdate = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
                'kubgtu_id' => $data['kubgtu_id'] ?? $user->kubgtu_id,
            ];

            // Update password if provided
            if (isset($data['password']) && ! empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }

            // Handle avatar upload
            if (isset($data['avatar'])) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    $this->fileService->deleteFile($user->avatar);
                }
                $userUpdate['avatar'] = $this->fileService->storeAvatar($data['avatar']);
            }

            $user->update($userUpdate);

            // Update profile based on user's role
            $this->updateUserProfile($user, $data);

            return $user->fresh();
        });
    }

    /**
     * Delete user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        // Check for active dependencies
        if ($user->hasRole('student')) {
            $activeCases = $user->caseApplicationsAsLeader()
                ->whereIn('status', ['pending', 'accepted'])
                ->count();

            if ($activeCases > 0) {
                throw new \Exception('Cannot delete user with active case applications');
            }
        }

        if ($user->hasRole('partner')) {
            $partner = $user->partner;
            if ($partner) {
                $activeCases = $partner->cases()
                    ->where('status', 'active')
                    ->count();

                if ($activeCases > 0) {
                    throw new \Exception('Cannot delete partner with active cases');
                }
            }
        }

        return $user->delete();
    }

    /**
     * Get filtered and paginated users
     */
    public function getFilteredUsers(array $filters): LengthAwarePaginator
    {
        $query = User::query();

        // Apply search filter
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('kubgtu_id', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if (isset($filters['role']) && ! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        // Apply status filter (active/inactive based on deleted_at)
        if (isset($filters['status'])) {
            if ($filters['status'] === 'inactive') {
                $query->onlyTrashed();
            } else {
                $query->whereNull('deleted_at');
            }
        }

        // Eager load relationships
        $query->with(['studentProfile', 'partnerProfile', 'teacherProfile', 'roles']);

        return $query->latest()->paginate(20);
    }

    /**
     * Get detailed user information with all relationships
     */
    public function getUserDetails(User $user): array
    {
        $user->load([
            'studentProfile',
            'partnerProfile',
            'teacherProfile',
            'skills',
            'badges',
            'caseApplicationsAsLeader.case',
            'roles',
        ]);

        $statistics = [];

        if ($user->hasRole('student')) {
            $statistics = [
                'total_points' => $user->studentProfile?->total_points ?? 0,
                'active_cases' => $user->caseApplicationsAsLeader()
                    ->where('status', 'accepted')
                    ->count(),
                'completed_cases' => $user->caseApplicationsAsLeader()
                    ->where('status', 'completed')
                    ->count(),
                'skills_count' => $user->skills()->count(),
                'badges_count' => $user->badges()->count(),
            ];
        } elseif ($user->hasRole('partner')) {
            $partner = $user->partner;
            $statistics = [
                'total_cases' => $partner?->cases()->count() ?? 0,
                'active_cases' => $partner?->cases()->where('status', 'active')->count() ?? 0,
                'completed_cases' => $partner?->cases()->where('status', 'completed')->count() ?? 0,
            ];
        }

        return [
            'user' => $user,
            'statistics' => $statistics,
        ];
    }

    /**
     * Update student profile
     */
    public function updateStudentProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user basic info
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ]);

            // Handle avatar
            if (isset($data['avatar'])) {
                if ($user->avatar) {
                    $this->fileService->deleteFile($user->avatar);
                }
                $user->update([
                    'avatar' => $this->fileService->storeAvatar($data['avatar']),
                ]);
            }

            // Update student profile
            $profile = $user->studentProfile;
            if ($profile) {
                $profile->update([
                    'faculty_id' => $data['faculty_id'] ?? $profile->faculty_id,
                    'course' => $data['course'] ?? $profile->course,
                    'group_number' => $data['group_number'] ?? $profile->group_number,
                    'bio' => $data['bio'] ?? $profile->bio,
                    'telegram' => $data['telegram'] ?? $profile->telegram,
                    'vk' => $data['vk'] ?? $profile->vk,
                ]);
            }

            return $user->fresh();
        });
    }

    /**
     * Update partner profile
     */
    public function updatePartnerProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user basic info
            $user->update([
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ]);

            // Update partner profile
            $profile = $user->partnerProfile;
            if ($profile) {
                $profile->update([
                    'position' => $data['position'] ?? $profile->position,
                    'phone' => $data['phone'] ?? $profile->phone,
                    'telegram' => $data['telegram'] ?? $profile->telegram,
                ]);
            }

            // Update partner company info
            $partner = $user->partner;
            if ($partner) {
                // Handle logo upload
                if (isset($data['logo'])) {
                    if ($partner->logo) {
                        $this->fileService->deleteFile($partner->logo);
                    }
                    $partner->logo = $this->fileService->storeLogo($data['logo']);
                }

                $partner->update([
                    'company_name' => $data['company_name'] ?? $partner->company_name,
                    'description' => $data['description'] ?? $partner->description,
                    'website' => $data['website'] ?? $partner->website,
                    'logo' => $partner->logo,
                ]);
            }

            return $user->fresh();
        });
    }

    /**
     * Create profile for user based on role
     */
    private function createProfileForUser(User $user, string $role, array $data): void
    {
        match ($role) {
            'student' => StudentProfile::create([
                'user_id' => $user->id,
                'faculty_id' => $data['faculty_id'] ?? null,
                'course' => $data['course'] ?? null,
                'group_number' => $data['group_number'] ?? null,
                'total_points' => 0,
            ]),
            'partner' => $this->createPartnerProfile($user, $data),
            'teacher' => TeacherProfile::create([
                'user_id' => $user->id,
                'faculty_id' => $data['faculty_id'] ?? null,
                'position' => $data['position'] ?? null,
            ]),
            default => throw new \InvalidArgumentException("Invalid role: {$role}"),
        };
    }

    /**
     * Create partner profile and partner company
     */
    private function createPartnerProfile(User $user, array $data): void
    {
        // Create Partner company first
        $partner = Partner::create([
            'company_name' => $data['company_name'] ?? 'Unknown Company',
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'logo' => null,
            'is_active' => true,
        ]);

        // Create PartnerProfile linking user to partner
        PartnerProfile::create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'position' => $data['position'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);
    }

    /**
     * Update user's profile based on role
     */
    private function updateUserProfile(User $user, array $data): void
    {
        if ($user->hasRole('student') && $user->studentProfile) {
            $this->updateStudentProfileData($user->studentProfile, $data);
        } elseif ($user->hasRole('partner') && $user->partnerProfile) {
            $this->updatePartnerProfileData($user, $data);
        } elseif ($user->hasRole('teacher') && $user->teacherProfile) {
            $user->teacherProfile->update([
                'faculty_id' => $data['faculty_id'] ?? $user->teacherProfile->faculty_id,
                'position' => $data['position'] ?? $user->teacherProfile->position,
            ]);
        }
    }

    /**
     * Update student profile data
     */
    private function updateStudentProfileData(StudentProfile $profile, array $data): void
    {
        $profile->update([
            'faculty_id' => $data['faculty_id'] ?? $profile->faculty_id,
            'course' => $data['course'] ?? $profile->course,
            'group_number' => $data['group_number'] ?? $profile->group_number,
            'bio' => $data['bio'] ?? $profile->bio,
            'telegram' => $data['telegram'] ?? $profile->telegram,
            'vk' => $data['vk'] ?? $profile->vk,
        ]);
    }

    /**
     * Update partner profile data
     */
    private function updatePartnerProfileData(User $user, array $data): void
    {
        $user->partnerProfile->update([
            'position' => $data['position'] ?? $user->partnerProfile->position,
            'phone' => $data['phone'] ?? $user->partnerProfile->phone,
            'telegram' => $data['telegram'] ?? $user->partnerProfile->telegram,
        ]);

        // Update partner company if exists
        $partner = $user->partner;
        if ($partner) {
            $partner->update([
                'company_name' => $data['company_name'] ?? $partner->company_name,
                'description' => $data['description'] ?? $partner->description,
                'website' => $data['website'] ?? $partner->website,
            ]);
        }
    }
}
