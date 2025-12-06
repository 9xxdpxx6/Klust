<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\FilterHelper;
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
                // Для нового пользователя нет старого аватара, но используем метод для консистентности
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

            // Update password if provided and different from current
            if (isset($data['password']) && ! empty($data['password'])) {
                if (! Hash::check($data['password'], $user->password)) {
                    $userUpdate['password'] = Hash::make($data['password']);
                }
            }

            // Handle avatar upload
            if (isset($data['avatar'])) {
                // Delete old avatar before uploading new one
                $this->updateUserAvatar($user, $data['avatar']);
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
            $activeCases = $user->caseApplications()
                ->where(function ($q) {
                    $q->pending()->orWhere(function ($subQ) {
                        $subQ->accepted();
                    });
                })
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
        $search = FilterHelper::getStringFilter($filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = FilterHelper::sanitizeSearch($search);
            $query->where(function ($q) use ($sanitizedSearch) {
                $q->where('name', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('email', 'like', "%{$sanitizedSearch}%")
                    ->orWhere('kubgtu_id', 'like', "%{$sanitizedSearch}%");
            });
        }

        // Apply role filter
        $role = FilterHelper::getStringFilter($filters['role'] ?? null);
        if ($role) {
            $query->role($role);
        }

        // Apply status filter (active/inactive based on deleted_at)
        $status = FilterHelper::getStringFilter($filters['status'] ?? null);
        if ($status === 'inactive') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->whereNull('deleted_at');
        }

        // Eager load relationships
        $query->with(['studentProfile', 'partnerProfile', 'teacherProfile', 'roles']);

        // Get pagination parameters
        $pagination = FilterHelper::getPaginationParams($filters, 20);

        return $query->latest()->paginate($pagination['per_page']);
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
            'caseApplications.case',
            'roles',
        ]);

        $statistics = [];

        if ($user->hasRole('student')) {
            $statistics = [
                'total_points' => $user->studentProfile?->total_points ?? 0,
                'active_cases' => $user->caseApplications()
                    ->accepted()
                    ->count(),
                'completed_cases' => $user->caseApplications()
                    ->completed()
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
                $this->updateUserAvatar($user, $data['avatar']);
                $user->update([
                    'avatar' => $this->fileService->storeAvatar($data['avatar']),
                ]);
            }

            // Update or create student profile
            $profile = $user->studentProfile;
            if ($profile) {
                $profile->update([
                    'faculty_id' => $data['faculty_id'] ?? $profile->faculty_id,
                    'course' => $data['course'] ?? $profile->course,
                    'group' => $data['group'] ?? $profile->group,
                    'specialization' => $data['specialization'] ?? $profile->specialization,
                    'bio' => $data['bio'] ?? $profile->bio,
                    'phone' => $data['phone'] ?? $profile->phone,
                ]);
            } else {
                // Create profile if it doesn't exist
                StudentProfile::create([
                    'user_id' => $user->id,
                    'faculty_id' => $data['faculty_id'] ?? null,
                    'course' => $data['course'] ?? null,
                    'group' => $data['group'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'total_points' => 0,
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
            $userUpdate = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ];

            // Handle avatar upload
            if (isset($data['avatar'])) {
                // Delete old avatar before uploading new one
                $this->updateUserAvatar($user, $data['avatar']);
                $userUpdate['avatar'] = $this->fileService->storeAvatar($data['avatar']);
            }

            $user->update($userUpdate);

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
     * Update user profile (for all roles: admin, teacher, student, partner)
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user basic info
            $updateData = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ];

            // Handle password update
            if (!empty($data['password'])) {
                $updateData['password'] = bcrypt($data['password']);
            }

            // Handle avatar - удаляем старый и сохраняем новый
            if (isset($data['avatar'])) {
                // Удаляем старый аватар перед сохранением нового
                $this->updateUserAvatar($user, $data['avatar']);
                
                // Если это файл (UploadedFile), сохраняем его
                if ($data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                    $updateData['avatar'] = $this->fileService->storeAvatar($data['avatar']);
                } elseif (is_string($data['avatar'])) {
                    // Если это уже путь (строка), используем его напрямую
                    $updateData['avatar'] = $data['avatar'];
                }
            }

            $user->update($updateData);

            // Обновляем профиль в зависимости от роли
            if ($user->hasRole('teacher') && $user->teacherProfile) {
                $profileData = [];
                if (isset($data['department'])) {
                    $profileData['department'] = $data['department'];
                }
                if (isset($data['position'])) {
                    $profileData['position'] = $data['position'];
                }
                if (isset($data['bio'])) {
                    $profileData['bio'] = $data['bio'];
                }
                if (isset($data['phone'])) {
                    // Для teacher phone можно хранить в users, но сейчас не используется
                }
                if (!empty($profileData)) {
                    $user->teacherProfile->update($profileData);
                }
            } elseif ($user->hasRole('student') && $user->studentProfile) {
                $profileData = [];
                if (isset($data['faculty_id'])) {
                    $profileData['faculty_id'] = $data['faculty_id'];
                }
                if (isset($data['group'])) {
                    $profileData['group'] = $data['group'];
                }
                if (isset($data['course'])) {
                    $profileData['course'] = $data['course'];
                }
                if (isset($data['specialization'])) {
                    $profileData['specialization'] = $data['specialization'];
                }
                if (isset($data['phone'])) {
                    $profileData['phone'] = $data['phone'];
                }
                if (isset($data['bio'])) {
                    $profileData['bio'] = $data['bio'];
                }
                if (!empty($profileData)) {
                    $user->studentProfile->update($profileData);
                }
            } elseif ($user->hasRole('partner') && $user->partnerProfile) {
                $profileData = [];
                if (isset($data['company_name'])) {
                    $profileData['company_name'] = $data['company_name'];
                }
                if (isset($data['inn'])) {
                    $profileData['inn'] = $data['inn'];
                }
                if (isset($data['address'])) {
                    $profileData['address'] = $data['address'];
                }
                if (isset($data['website'])) {
                    $profileData['website'] = $data['website'];
                }
                if (isset($data['description'])) {
                    $profileData['description'] = $data['description'];
                }
                if (isset($data['contact_person'])) {
                    $profileData['contact_person'] = $data['contact_person'];
                }
                if (isset($data['contact_phone'])) {
                    $profileData['contact_phone'] = $data['contact_phone'];
                }
                if (!empty($profileData)) {
                    $user->partnerProfile->update($profileData);
                }
            }

            // Загружаем все профили для возврата
            $relations = ['roles'];
            if ($user->hasRole('teacher')) {
                $relations[] = 'teacherProfile';
            }
            if ($user->hasRole('student')) {
                $relations[] = 'studentProfile.faculty';
            }
            if ($user->hasRole('partner')) {
                $relations[] = 'partnerProfile';
            }

            return $user->fresh($relations);
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
                'group' => $data['group'] ?? null,
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

    /**
     * Delete old user avatar before uploading new one
     * Uses getRawOriginal to get the path from DB, not the URL accessor
     */
    private function updateUserAvatar(User $user, $newAvatar): void
    {
        // Получаем оригинальный путь из БД (не URL через accessor)
        $oldAvatarPath = $user->getRawOriginal('avatar');
        if ($oldAvatarPath) {
            $this->fileService->deleteFile($oldAvatarPath);
        }
    }

    /**
     * Delete user avatar from storage and database
     * Public method for use in controllers
     */
    public function deleteUserAvatar(User $user): void
    {
        if ($user->avatar) {
            // Удаляем файл из хранилища
            $oldAvatarPath = $user->getRawOriginal('avatar');
            if ($oldAvatarPath) {
                $this->fileService->deleteFile($oldAvatarPath);
            }
            
            // Удаляем из БД
            $user->update(['avatar' => null]);
        }
    }
}
