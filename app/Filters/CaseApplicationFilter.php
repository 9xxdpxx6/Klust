<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

final class CaseApplicationFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = [
        'id',
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
        if (! $this->hasFilter('search')) {
            return;
        }

        $searchTerm = (string) $this->getFilter('search');
        
        $words = array_filter(
            array_map('trim', explode(' ', $searchTerm)),
            fn (string $word): bool => mb_strlen($word) >= 2
        );
        
        if (empty($words)) {
            return;
        }
        
        $query->where(function ($q) use ($words, $searchTerm) {
            // Поиск по мотивации
            foreach ($words as $word) {
                $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                $q->orWhere('motivation', 'like', '%'.$escapedWord.'%');
            }
            
            // Поиск по лидеру (имя, email)
            $q->orWhereHas('leader', function ($leaderQuery) use ($words) {
                foreach ($words as $word) {
                    $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                    $leaderQuery->where(function ($lq) use ($escapedWord) {
                        $lq->where('name', 'like', '%'.$escapedWord.'%')
                           ->orWhere('email', 'like', '%'.$escapedWord.'%');
                    });
                }
            });
            
            // Поиск по названию кейса
            $q->orWhereHas('case', function ($caseQuery) use ($words) {
                foreach ($words as $word) {
                    $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                    $caseQuery->where('title', 'like', '%'.$escapedWord.'%');
                }
            });
            
            // Поиск по именам участников команды
            $q->orWhereHas('teamMembers.user', function ($memberQuery) use ($words) {
                foreach ($words as $word) {
                    $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                    $memberQuery->where('name', 'like', '%'.$escapedWord.'%');
                }
            });
        });
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
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $status) {
                    $q->orWhereHas('status', function ($statusQuery) use ($status) {
                        $statusQuery->where('name', $status);
                    });
                }
            });
        } else {
            $query->withStatus($statuses);
        }
    }

    private function applyCaseFilter(Builder $query): void
    {
        if (! $this->hasFilter('case_id')) {
            return;
        }

        $caseId = (int) $this->getFilter('case_id');

        if ($caseId > 0) {
            $query->where('case_id', $caseId);
        }
    }

    private function applySubmittedAtRangeFilter(Builder $query): void
    {
        // Support both submitted_from/to and date_from/to
        $dateFrom = $this->getFilter('submitted_from') ?? $this->getFilter('date_from');
        $dateTo = $this->getFilter('submitted_to') ?? $this->getFilter('date_to');

        if ($dateFrom) {
            $query->whereDate('submitted_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('submitted_at', '<=', $dateTo);
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

        // Для сортировки по created_at используем поле created_at
        if ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
