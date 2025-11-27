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

        $search = (string) $this->getFilter('search');

        $query->where(function ($q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('kubgtu_id', 'like', "%{$search}%");

            // Поиск по числовому ID, если поисковый запрос - число
            if (is_numeric($search)) {
                $q->orWhere('id', (int) $search);
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

        $query->where('course', (int) $course);
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

