# Add a CI pipeline (GitHub Actions)

**Priority:** High
**Labels:** ci, infrastructure, good first issue

## Summary

There is currently no continuous integration. Every pull request should be
automatically checked so that style and test regressions are caught before merge.

## Proposed Work

- Add `.github/workflows/ci.yml` running on `push` and `pull_request`.
- Set up PHP 8.1+ with required extensions (`dom`, `pdo`), Composer cache, and
  Node 18+ with npm cache.
- Steps:
  - `composer install --no-interaction --prefer-dist`
  - `cp .env.example .env && php artisan key:generate`
  - `./vendor/bin/pint --test` (style check, non-mutating)
  - `npm ci && npm run build`
  - `php artisan test`
- Use a MySQL/MariaDB service container (or SQLite for speed) for the test job.

## Acceptance Criteria

- CI runs and passes on `main`.
- A failing Pint check or failing test blocks the PR.
- README gets a build-status badge.
