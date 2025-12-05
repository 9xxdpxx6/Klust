<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class CaseFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = [
        'created_at',
        'deadline',
        'title',
        'status',
    ];

    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyStatusFilter($query);
        $this->applySimulatorFilter($query);
        $this->applyPartnerFilter($query);
        $this->applySkillsFilter($query);
        $this->applyDeadlineRangeFilter($query);
        $this->applyCreatedAtRangeFilter($query);
        $this->applySortingFilter($query);

        return $query;
    }

    protected function getDefaultPerPage(): int
    {
        return 15;
    }

    private function applySearchFilter(Builder $query): void
    {
        if (! $this->hasFilter('search')) {
            return;
        }

        $searchTerm = (string) $this->getFilter('search');

        $this->applySmartSearch($query, ['title', 'description'], $searchTerm);
    }

    private function applyStatusFilter(Builder $query): void
    {
        if (! $this->hasFilter('status')) {
            return;
        }

        $statuses = $this->getFilter('status');

        if (is_array($statuses)) {
            $statuses = array_filter($statuses, static fn ($status): bool => $status !== null && $status !== '');
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

    private function applySimulatorFilter(Builder $query): void
    {
        if (! $this->hasFilter('simulator_id')) {
            return;
        }

        $query->where('simulator_id', (int) $this->getFilter('simulator_id'));
    }

    private function applyPartnerFilter(Builder $query): void
    {
        if (! $this->hasFilter('partner_id')) {
            return;
        }

        // Проверяем, не установлено ли уже условие partner_id в запросе
        // Если установлено, не перезаписываем его (защита от перезаписи)
        $wheres = $query->getQuery()->wheres ?? [];
        $hasPartnerIdCondition = false;
        
        foreach ($wheres as $where) {
            if (isset($where['column']) && $where['column'] === 'partner_id') {
                $hasPartnerIdCondition = true;
                break;
            }
        }

        // Применяем фильтр только если условие partner_id еще не установлено
        if (! $hasPartnerIdCondition) {
            $query->where('partner_id', (int) $this->getFilter('partner_id'));
        }
    }

    private function applySkillsFilter(Builder $query): void
    {
        if (! $this->hasFilter('skills')) {
            return;
        }

        $skills = $this->getFilter('skills');

        if (is_array($skills) && ! empty($skills)) {
            $skills = array_filter($skills, static fn ($skill): bool => is_numeric($skill));

            if (! empty($skills)) {
                $query->whereHas('skills', function (Builder $skillQuery) use ($skills): void {
                    $skillQuery->whereIn('skills.id', $skills);
                });
            }
        }
    }

    private function applyDeadlineRangeFilter(Builder $query): void
    {
        $from = $this->getFilter('deadline_from');
        $to = $this->getFilter('deadline_to');

        if ($from) {
            $query->whereDate('deadline', '>=', $from);
        }

        if ($to) {
            $query->whereDate('deadline', '<=', $to);
        }
    }

    private function applyCreatedAtRangeFilter(Builder $query): void
    {
        $from = $this->getFilter('date_from');
        $to = $this->getFilter('date_to');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
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
