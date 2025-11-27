<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class SkillFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = [
        'name',
        'category',
        'max_level',
        'created_at',
    ];

    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyCategoryFilter($query);
        $this->applySortingFilter($query);

        return $query;
    }

    protected function getDefaultPerPage(): int
    {
        return 15;
    }

    private function applySearchFilter(Builder $query): void
    {
        $this->applySearch($query, 'name');
    }

    private function applyCategoryFilter(Builder $query): void
    {
        if (! $this->hasFilter('category')) {
            return;
        }

        $category = (string) $this->getFilter('category');

        if (! empty($category) && in_array($category, ['hard', 'soft', 'language', 'other'], true)) {
            $query->where('category', $category);
        }
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
