# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- See [ROADMAP.md](ROADMAP.md) for planned work.

## [0.1.0] - 2026-06-14

First public open-source release.

### Added

- **Role-based platform** with four roles (`student`, `teacher`, `partner`, `admin`) and three interfaces: admin panel (`/admin`), student portal (`/student`), and partner portal (`/partner`), built on Laravel 10 + Vue 3 + Inertia.js.
- **Authentication** via Laravel Sanctum, role-based registration (student/partner), and email verification.
- **Authorization** using Spatie Laravel Permission with seeded roles and permissions.
- **Case management**: partners and admins can create, edit, publish, and archive cases with required skills and team sizes.
- **Applications & teams**: students apply to cases as team leaders, invite members up to the case's required team size, and partners approve/reject applications with status history.
- **Gamification**: skills with levels and points, automatic badges, certificates, and configurable skill reward rules/events.
- **Bank consultant simulator**: client generation, branching dialogue, credit and deposit calculators, scoring, and evaluation services, with a Three.js scene.
- **Partner analytics** dashboard with Excel exports for cases, applications, and teams.
- **Public site**: home, about, case catalog, partner directory, achievements, and "how it works" pages.
- **Notification center** with in-app notifications.
- **Demo data seeders** populating students, partners, teachers, cases, applications, skills, badges, simulator sessions, and notifications.
- Project documentation: README, ROADMAP, CONTRIBUTING, MIT LICENSE, and simulator/testing docs under `docs/`.

[Unreleased]: https://github.com/<your-org>/klust/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/<your-org>/klust/releases/tag/v0.1.0
