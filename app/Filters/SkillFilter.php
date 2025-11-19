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
        $sortBy = $this->getFilter('sort_by', 'name');
        $sortOrder = strtolower((string) $this->getFilter('sort_order', 'asc'));

        if (! in_array($sortBy, self::ALLOWED_SORT_FIELDS, true)) {
            $sortBy = 'name';
        }

        if (! in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }
}
