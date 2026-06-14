# Contributing to Klust

Thank you for your interest in contributing to **Klust**! This document explains how to set up the project locally, the coding standards we follow, and how to submit your changes. Contributions of all kinds — bug fixes, features, tests, documentation, and translations — are welcome.

## Code of Conduct

This project follows a [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold it: be respectful and constructive, assume good intent, keep discussions focused on the work, and help make this a welcoming community for contributors of all backgrounds.

## Getting Started

### Prerequisites

- PHP **8.1+** with `ext-dom` and `ext-pdo`
- Composer
- Node.js 18+ and npm
- MySQL or MariaDB

### Local Setup

```bash
# 1. Fork the repository on GitHub, then clone your fork
git clone https://github.com/<your-username>/Klust.git
cd Klust

# (optional) point "upstream" at the canonical repository
git remote add upstream https://github.com/9xxdpxx6/Klust.git

# 2. Install dependencies
composer install
npm install

# 3. Create your environment file and generate a key
cp .env.example .env
php artisan key:generate

# 4. Configure the database connection in .env
#    (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 5. Run migrations with the demo seed data
php artisan migrate --seed

# 6. Start the dev servers (two terminals)
npm run dev          # Vite (hot module replacement)
php artisan serve    # Laravel application server
```

The seeded dataset gives you working accounts across all roles (student, partner, teacher, admin) so you can exercise every interface.

## Project Conventions

Klust is a Laravel + Vue 3 + Inertia.js monolith. Please keep changes consistent with the existing architecture (see [README.md](README.md) and `CLAUDE.md` for the full overview).

### PHP / Laravel

- Add `declare(strict_types=1);` to the top of every new PHP file.
- Follow **PSR-12** and Laravel conventions.
- Type-hint all method parameters and return types.
- Keep **controllers thin** — they handle HTTP only. Put business logic in **service classes** (`app/Services/`).
- Validate input with **Form Request** classes (`app/Http/Requests/{Role}/{Resource}/{Action}Request.php`), never inline in controllers.
- Wrap multi-table writes in `DB::transaction()`.
- Prevent N+1 queries with eager loading (`with()`).
- This is **not** a REST API — render pages with `Inertia::render(...)` and define routes in `routes/web.php`. (`routes/api.php` contains only Laravel's default stub and is unused; don't build features on it.)

### Vue / Frontend

- Use the **Composition API** with `<script setup>` (no Options API).
- Pages live in `resources/js/Pages/`, reusable components in `resources/js/Components/`, layouts in `resources/js/Layouts/`.
- Style with **TailwindCSS**; use **PrimeVue** for complex UI components.
- Use the Inertia `useForm` helper for form submissions and **Ziggy** (`route(...)`) for named routes.

### Database

- Create schema changes with migrations (`php artisan make:migration ...`) — always commit migration files.
- Name pivot tables with singular model names in alphabetical order (e.g. `case_skills`, `user_skills`).
- Provide seeders/factories for any new demo data.

### Tooling

Before opening a pull request, run:

```bash
# Format code to project style
./vendor/bin/pint

# Run the test suite
php artisan test
```

Add tests for new behavior where practical — feature tests for user flows and unit tests for service logic are especially valued, since test coverage is an active area of improvement.

## Submitting Changes

1. **Create a branch** from `main` using a descriptive name:
   - `feat/...` for features, `fix/...` for bug fixes, `docs/...` for documentation, `test/...` for tests, `refactor/...` for refactors.
2. **Make focused commits** with clear, imperative messages (e.g. `fix: prevent duplicate case applications`). Keep unrelated changes in separate pull requests.
3. **Run Pint and the tests** locally and make sure they pass.
4. **Open a pull request** against `main` with:
   - a clear description of *what* changed and *why*;
   - linked issue number(s) if applicable;
   - screenshots or screen recordings for UI changes.
5. **Respond to review feedback** and update your branch as needed. Maintainers may request changes before merging.

## Reporting Bugs & Requesting Features

- Search existing issues first to avoid duplicates.
- For **bugs**, include reproduction steps, expected vs. actual behavior, and your environment (PHP, Node, DB versions).
- For **features**, describe the problem you're solving and the proposed approach.

## Security

If you discover a security vulnerability, please **do not** open a public issue. Follow the private disclosure process described in [SECURITY.md](SECURITY.md) so it can be addressed responsibly.

---

Thanks again for contributing to Klust!
