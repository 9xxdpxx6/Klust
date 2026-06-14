# Dockerized setup and deployment documentation

**Priority:** Medium
**Labels:** infrastructure, documentation, onboarding

## Summary

Laravel Sail is listed as a dependency but there is no documented one-command
container workflow, and there is no production deployment guide. A reproducible
Docker setup lowers the barrier for contributors who don't want to install PHP,
Node, and MySQL locally.

## Proposed Work

- Verify/complete the Sail configuration and document the full container-based
  setup (`./vendor/bin/sail up`, migrate, seed, build).
- Add a short production deployment guide (build assets, cache config/routes,
  run migrations, queue worker, web server notes).
- Cross-link the Docker workflow from the README "Local Development Setup".

## Acceptance Criteria

- A contributor can run the app via containers using documented steps.
- A basic, accurate production deployment checklist exists in `docs/`.
