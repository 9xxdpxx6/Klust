# Add feature tests for core user flows

**Priority:** High
**Labels:** tests, quality

## Summary

The `tests/` directory currently contains only the default Laravel example
tests (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). Critical
flows are untested, which makes contributions risky.

## Proposed Work

Add feature tests (using factories and the existing seeders where helpful) for:

- Authentication: registration (student/partner), login, email verification.
- Case application: a student creates an application as team leader and adds
  members up to `required_team_size`.
- Partner approval/rejection: a partner accepts or rejects an application and
  status history is recorded.
- Authorization: a partner cannot edit a case they do not own.

## Acceptance Criteria

- New tests live under `tests/Feature/` and pass via `php artisan test`.
- The default `ExampleTest` files are removed or replaced.
- Tests run on a transactional/refreshing test database.
