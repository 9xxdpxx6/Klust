# Audit and fix N+1 queries on dashboards and analytics

**Priority:** Medium
**Labels:** performance, backend

## Summary

Dashboards and the partner analytics views aggregate across users, cases,
applications, and teams. Without consistent eager loading these are prone to
N+1 query problems as the dataset grows.

## Proposed Work

- Use Laravel Debugbar or `DB::listen` against the seeded dataset to find N+1
  hotspots in `DashboardService`, `AnalyticsService`, and the admin/partner
  index controllers.
- Add `with()` eager loading and `select()` column narrowing where appropriate.
- Consider caching expensive dashboard aggregates.

## Acceptance Criteria

- Key dashboard and analytics pages issue a bounded number of queries
  regardless of row count.
- No regressions in displayed data.
