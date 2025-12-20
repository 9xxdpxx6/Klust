<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class UserFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = [
        'created_at',
        'name',
        'email',
    ];

    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyRoleFilter($query);
        $this->applyStatusFilter($query);
        $this->applyCourseFilter($query);
        $this->applySortingFilter($query);

        return $query;
    }

    protected function getDefaultPerPage(): int
    {
        return 25;
    }

    private function applySearchFilter(Builder $query): void
    {
        if (! $this->hasFilter('search')) {
            return;
        }

        $search = trim((string) $this->getFilter('search'));

        if (empty($search)) {
            return;
        }

        // Разбиваем поисковый запрос на отдельные слова
        $keywords = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

        // Если поисковый запрос - одно число, ищем по ID
        if (count($keywords) === 1 && is_numeric($search)) {
        $query->where(function ($q) use ($search): void {
                $q->where('id', (int) $search)
                ->orWhere('kubgtu_id', 'like', "%{$search}%");
            });
            return;
        }

        // Для каждого ключевого слова создаем условие поиска
        // Все слова должны совпадать (AND логика)
        $query->where(function ($q) use ($keywords): void {
            foreach ($keywords as $keyword) {
                $q->where(function ($wordQuery) use ($keyword): void {
                    // Поиск по основным полям пользователя
                    $wordQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('kubgtu_id', 'like', "%{$keyword}%");

                    // Поиск по профилю партнера
                    $wordQuery->orWhereHas('partnerProfile', function ($profileQuery) use ($keyword): void {
                        $profileQuery->where('company_name', 'like', "%{$keyword}%")
                            ->orWhere('contact_person', 'like', "%{$keyword}%")
                            ->orWhere('contact_phone', 'like', "%{$keyword}%")
                            ->orWhere('inn', 'like', "%{$keyword}%");
                    });

                    // Поиск по профилю студента
                    $wordQuery->orWhereHas('studentProfile', function ($profileQuery) use ($keyword): void {
                        $profileQuery->where('group', 'like', "%{$keyword}%")
                            ->orWhere('specialization', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });

                    // Поиск по профилю преподавателя
                    $wordQuery->orWhereHas('teacherProfile', function ($profileQuery) use ($keyword): void {
                        $profileQuery->where('department', 'like', "%{$keyword}%")
                            ->orWhere('position', 'like', "%{$keyword}%");
                    });
                });
            }
        });
    }

    private function applyRoleFilter(Builder $query): void
    {
        if (! $this->hasFilter('role')) {
            return;
        }

        $role = (string) $this->getFilter('role');

        if (empty($role)) {
            return;
        }

        $query->whereHas('roles', function ($q) use ($role): void {
            $q->where('name', $role);
        });
    }

    private function applyStatusFilter(Builder $query): void
    {
        if (! $this->hasFilter('status')) {
            return;
        }

        $status = (string) $this->getFilter('status');

        if ($status === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($status === 'unverified') {
            $query->whereNull('email_verified_at');
        }
    }

    private function applyCourseFilter(Builder $query): void
    {
        if (! $this->hasFilter('course')) {
            return;
        }

        $course = $this->getFilter('course');

        if (empty($course)) {
            return;
        }

        $query->whereHas('studentProfile', function ($q) use ($course): void {
            $q->where('course', (int) $course);
        });
    }

    private function applySortingFilter(Builder $query): void
    {
        $sortBy = $this->getFilter('sort_by');

        // If sort_by is not provided or not in allowed fields, use id desc by default
        if (! $sortBy || ! in_array($sortBy, self::ALLOWED_SORT_FIELDS, true)) {
            $sortBy = 'id';
        }

        $sortOrder = strtolower((string) $this->getFilter('sort_order', 'desc'));

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }
}

