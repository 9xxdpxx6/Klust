## Description

What does this PR change, and why? Summarize the problem and the approach.

## Related Issues

Closes #(issue number) <!-- or "Refs #..." if it doesn't fully close it -->

## Type of Change

- [ ] Bug fix
- [ ] New feature
- [ ] Refactor (no functional change)
- [ ] Documentation
- [ ] Tests
- [ ] Build / tooling

## Affected Interface(s)

- [ ] Admin panel
- [ ] Student portal
- [ ] Partner portal
- [ ] Public site
- [ ] Shared / infrastructure

## Checklist

- [ ] New PHP files declare `declare(strict_types=1);` and follow PSR-12
- [ ] Business logic lives in a service; controllers stay thin
- [ ] Input is validated through Form Request classes
- [ ] `./vendor/bin/pint` passes
- [ ] `php artisan test` passes
- [ ] Migrations/seeders/factories are included for schema changes
- [ ] Documentation updated where relevant (README, CHANGELOG, docs/)

## Screenshots / Recordings

For UI changes, include before/after screenshots or a short recording.

## Notes for Reviewers

Anything reviewers should focus on, known limitations, or follow-ups.
