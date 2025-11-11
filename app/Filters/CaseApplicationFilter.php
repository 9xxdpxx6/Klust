<?php

declare(strict_types=1);

namespace App\Filters;

use App\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

final class CaseApplicationFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = [
        'submitted_at',
        'created_at',
        'status',
    ];

    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyStatusFilter($query);
        $this->applyCaseFilter($query);
        $this->applySubmittedAtRangeFilter($query);
        $this->applySortingFilter($query);

        return $query;
    }

    protected function getDefaultPerPage(): int
    {
        return 20;
    }

    private function applySearchFilter(Builder $query): void
    {
        if (!$this->hasFilter('search')) {
            return;
        }

        $searchTerm = (string) $this->getFilter('search');
        $this->applySmartSearch($query, ['motivation'], $searchTerm);
        $this->applySmartSearchInRelationOr($query, 'leader', ['name', 'email'], $searchTerm);
        $this->applySmartSearchInRelationOr($query, 'case', ['title'], $searchTerm);
    }

    private function applyStatusFilter(Builder $query): void
    {
        if (!$this->hasFilter('status')) {
            return;
        }

        $statuses = $this->getFilter('status');

        if (is_array($statuses)) {
            $statuses = array_filter($statuses, static fn($status): bool => $status !== null && $status !== '');
        }

        if (empty($statuses)) {
            return;
        }

        if (is_array($statuses)) {
            $query->whereIn('status', $statuses);
        } else {
            $query->where('status', $statuses);
        }
    }

    private function applyCaseFilter(Builder $query): void
    {
        if (!$this->hasFilter('case_id')) {
            return;
        }

        $caseId = (int) $this->getFilter('case_id');

        if ($caseId > 0) {
            $query->where('case_id', $caseId);
        }
    }

    private function applySubmittedAtRangeFilter(Builder $query): void
    {
        $from = $this->getFilter('submitted_from') ?? $this->getFilter('date_from');
        $to = $this->getFilter('submitted_to') ?? $this->getFilter('date_to');

        if ($from) {
            $query->where('submitted_at', '>=', $from);
        }

        if ($to) {
            $query->where('submitted_at', '<=', $to);
        }
    }

    private function applySortingFilter(Builder $query): void
    {
        $sortBy = $this->getFilter('sort_by', 'submitted_at');
        $sortOrder = strtolower((string) $this->getFilter('sort_order', 'desc'));

        if (!in_array($sortBy, self::ALLOWED_SORT_FIELDS, true)) {
            $sortBy = 'submitted_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }

}

