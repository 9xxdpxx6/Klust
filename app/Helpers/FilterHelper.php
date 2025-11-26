<?php

declare(strict_types=1);

namespace App\Helpers;

use Carbon\Carbon;

class FilterHelper
{
    /**
     * Check if filter value is set and not empty
     */
    public static function hasValue(mixed $filter): bool
    {
        return isset($filter) && $filter !== '' && $filter !== null && $filter !== [];
    }

    /**
     * Get string filter value or default
     */
    public static function getStringFilter(mixed $value, ?string $default = null): ?string
    {
        return self::hasValue($value) ? (string) $value : $default;
    }

    /**
     * Get array filter value or default
     */
    public static function getArrayFilter(mixed $value, array $default = []): array
    {
        if (! self::hasValue($value)) {
            return $default;
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Get integer filter value or default
     */
    public static function getIntegerFilter(mixed $value, ?int $default = null): ?int
    {
        if (! self::hasValue($value) || ! is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Get boolean filter value or default
     */
    public static function getBooleanFilter(mixed $value, ?bool $default = null): ?bool
    {
        if (! self::hasValue($value)) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get date filter value or default
     */
    public static function getDateFilter(mixed $value, ?Carbon $default = null): ?Carbon
    {
        if (! self::hasValue($value)) {
            return $default;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Sanitize search string (escape special LIKE characters)
     */
    public static function sanitizeSearch(string $search): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $search);
    }

    /**
     * Get pagination parameters
     */
    public static function getPaginationParams(array $filters, int $defaultPerPage = 20): array
    {
        // ИЗМЕНЕНИЕ: Ищем сначала 'per_page', потом 'perPage'. Это делает код гибким.
        $perPageValue = $filters['per_page'] ?? $filters['perPage'] ?? null;

        $perPage = self::getIntegerFilter($perPageValue, $defaultPerPage);
        $perPage = min(max($perPage, 5), 100); // Clamp between 5 and 100

        return [
            'per_page' => $perPage, // Всегда возвращаем с 'per_page' для консистентности
            'page' => self::getIntegerFilter($filters['page'] ?? null, 1),
        ];
    }

    /**
     * Get sort parameters
     */
    public static function getSortParams(array $filters, string $defaultSort = 'created_at', string $defaultOrder = 'desc'): array
    {
        $sortBy = self::getStringFilter($filters['sort_by'] ?? null, $defaultSort);
        $sortOrder = self::getStringFilter($filters['sort_order'] ?? null, $defaultOrder);

        // Validate sort order
        $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? strtolower($sortOrder) : $defaultOrder;

        return [
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];
    }
}
