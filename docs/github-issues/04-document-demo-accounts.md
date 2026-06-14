# Document seeded demo accounts and how to log in as each role

**Priority:** High
**Labels:** documentation, good first issue, onboarding

## Summary

`php artisan migrate --seed` creates a rich demo dataset, but contributors have
no documented way to log in as a student, partner, teacher, or admin. This is a
significant onboarding barrier.

## Proposed Work

- Inspect `database/seeders/` to find (or define deterministic) credentials for
  one account per role.
- Document email/password for each role in the README (or a dedicated
  `docs/demo-accounts.md`).
- If the seeders use random credentials, add a small, clearly-marked set of
  fixed demo accounts for local use only.

## Acceptance Criteria

- A new contributor can run the seeders and immediately log into all four
  interfaces using documented credentials.
