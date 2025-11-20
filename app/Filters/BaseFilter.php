<?php

declare(strict_types=1);

namespace App\Filters;

use App\Filters\Contracts\FilterInterface;
use App\Helpers\FilterHelper;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseFilter implements FilterInterface
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    abstract public function apply(Builder $query): Builder;

    protected function applySearch(Builder $query, string $field = 'name'): void
    {
        $search = FilterHelper::getStringFilter($this->filters['search'] ?? null);
        if ($search) {
            $sanitizedSearch = FilterHelper::sanitizeSearch($search);
            $query->where($field, 'like', '%'.$sanitizedSearch.'%');
        }
    }

    /**
     * Умный поиск по словам: разбивает поисковую строку на слова
     * и ищет записи, содержащие все слова (в любом порядке).
     *
     * Пример: "жим лежа" найдет "жим штанги лежа", "лежа жим" и т.д.
     *
     * @param  array<string>  $fields  Массив полей для поиска
     * @param  string  $searchTerm  Поисковая строка
     * @param  int  $minWordLength  Минимальная длина слова для поиска
     */
    protected function applySmartSearch(Builder $query, array $fields, string $searchTerm, int $minWordLength = 2): void
    {
        if (empty($searchTerm) || empty($fields)) {
            return;
        }

        $words = array_filter(
            array_map('trim', explode(' ', $searchTerm)),
            fn (string $word): bool => mb_strlen($word) >= $minWordLength
        );

        if (empty($words)) {
            return;
        }

        $query->where(function ($q) use ($words, $fields): void {
            foreach ($fields as $field) {
                $q->orWhere(function ($fieldQuery) use ($words, $field): void {
                    foreach ($words as $word) {
                        $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                        $fieldQuery->where($field, 'like', '%'.$escapedWord.'%');
                    }
                });
            }
        });
    }

    /**
     * Умный поиск по словам для отношений (whereHas).
     *
     * @param  string  $relation  Название отношения
     * @param  array<string>  $fields  Массив полей для поиска в отношении
     * @param  string  $searchTerm  Поисковая строка
     * @param  int  $minWordLength  Минимальная длина слова для поиска
     */
    protected function applySmartSearchInRelation(
        Builder $query,
        string $relation,
        array $fields,
        string $searchTerm,
        int $minWordLength = 2
    ): void {
        if (empty($searchTerm) || empty($fields)) {
            return;
        }

        $words = array_filter(
            array_map('trim', explode(' ', $searchTerm)),
            fn (string $word): bool => mb_strlen($word) >= $minWordLength
        );

        if (empty($words)) {
            return;
        }

        $query->whereHas($relation, function ($relationQuery) use ($words, $fields): void {
            foreach ($fields as $field) {
                $relationQuery->orWhere(function ($fieldQuery) use ($words, $field): void {
                    foreach ($words as $word) {
                        $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                        $fieldQuery->where($field, 'like', '%'.$escapedWord.'%');
                    }
                });
            }
        });
    }

    /**
     * Умный поиск по словам для отношений с OR логикой (orWhereHas).
     * Используется для объединения нескольких условий поиска через OR.
     *
     * @param  string  $relation  Название отношения
     * @param  array<string>  $fields  Массив полей для поиска в отношении
     * @param  string  $searchTerm  Поисковая строка
     * @param  int  $minWordLength  Минимальная длина слова для поиска
     */
    protected function applySmartSearchInRelationOr(
        Builder $query,
        string $relation,
        array $fields,
        string $searchTerm,
        int $minWordLength = 2
    ): void {
        if (empty($searchTerm) || empty($fields)) {
            return;
        }

        $words = array_filter(
            array_map('trim', explode(' ', $searchTerm)),
            fn (string $word): bool => mb_strlen($word) >= $minWordLength
        );

        if (empty($words)) {
            return;
        }

        $query->orWhereHas($relation, function ($relationQuery) use ($words, $fields): void {
            foreach ($fields as $field) {
                $relationQuery->orWhere(function ($fieldQuery) use ($words, $field): void {
                    foreach ($words as $word) {
                        $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                        $fieldQuery->where($field, 'like', '%'.$escapedWord.'%');
                    }
                });
            }
        });
    }

    /**
     * Умный поиск по словам с OR логикой (orWhere).
     * Используется для объединения нескольких условий поиска через OR.
     *
     * @param  array<string>  $fields  Массив полей для поиска
     * @param  string  $searchTerm  Поисковая строка
     * @param  int  $minWordLength  Минимальная длина слова для поиска
     */
    protected function applySmartSearchOr(
        Builder $query,
        array $fields,
        string $searchTerm,
        int $minWordLength = 2
    ): void {
        if (empty($searchTerm) || empty($fields)) {
            return;
        }

        $words = array_filter(
            array_map('trim', explode(' ', $searchTerm)),
            fn (string $word): bool => mb_strlen($word) >= $minWordLength
        );

        if (empty($words)) {
            return;
        }

        $query->orWhere(function ($q) use ($words, $fields): void {
            foreach ($fields as $field) {
                $q->orWhere(function ($fieldQuery) use ($words, $field): void {
                    foreach ($words as $word) {
                        $escapedWord = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
                        $fieldQuery->where($field, 'like', '%'.$escapedWord.'%');
                    }
                });
            }
        });
    }

    protected function applySorting(Builder $query, string $defaultField = 'name', string $defaultOrder = 'asc'): void
    {
        $sortParams = FilterHelper::getSortParams($this->filters, $defaultField, $defaultOrder);
        $query->orderBy($sortParams['sort_by'], $sortParams['sort_order']);
    }

    protected function applyDateRange(Builder $query, string $field = 'created_at'): void
    {
        $dateFrom = FilterHelper::getDateFilter($this->filters['date_from'] ?? null);
        if ($dateFrom) {
            $query->where($field, '>=', $dateFrom);
        }

        $dateTo = FilterHelper::getDateFilter($this->filters['date_to'] ?? null);
        if ($dateTo) {
            $query->where($field, '<=', $dateTo);
        }
    }

    protected function hasFilter(string $key): bool
    {
        return FilterHelper::hasValue($this->filters[$key] ?? null);
    }

    protected function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    public function getPaginationParams(): array
    {
        return FilterHelper::getPaginationParams($this->filters, $this->getDefaultPerPage());
    }

    protected function getDefaultPerPage(): int
    {
        return 100;
    }
}
