# Klust (Кластер)

> An educational case-based learning platform that connects university students with industry partners for real-world project experience.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9)](https://inertiajs.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://www.php.net)

## Overview

**Klust** is a monolithic web application built on Laravel and Vue 3 (via Inertia.js) that brings together three groups of users — **students**, **industry partners**, and **university staff** — around real, case-based projects.

Partners publish practical cases (project briefs). Students assemble teams, apply to cases, and develop in-demand skills. University staff (admins and teachers) manage the catalog, users, skills, badges, and educational simulators. Progress is gamified through a skill-point and badge system, and students can practise applied scenarios — such as the included **bank financial-consultant simulator** — directly in the browser.

## Problem Statement

University curricula often leave a gap between theory and the practical experience employers expect. Students graduate without portfolio-worthy project work, while companies struggle to discover and evaluate emerging talent early. Coordinating this collaboration manually — matching students to projects, tracking team applications, and assessing skill growth — is slow and hard to scale.

Klust closes that gap by providing a single platform where:

- partners describe concrete problems as **cases** with required skills and team sizes;
- students form **teams**, apply, and build a verifiable record of skills and achievements;
- staff oversee the process and measure outcomes with built-in analytics;
- applied learning is reinforced through **interactive simulators** that award skill points.

## Key Features

### For Students
- Browse a catalog of partner cases, including **recommended** cases matched to the student's skills
- Create applications as a **team leader** and invite team members (up to the case's required team size, max 10)
- Track personal applications and team membership
- Develop and showcase **skills** (with levels and points) and earn **badges** automatically
- Practise applied scenarios in **simulators**, including a multi-stage **bank consultant simulator** (client dialogue, credit/deposit calculators, scoring and evaluation)
- Receive in-app **notifications** and view a personal dashboard

### For Partners
- Publish, edit, and archive **cases** with required skills and team sizes
- Review incoming **applications**, approve or reject them with comments, and manage application status history
- View **teams** and individual student profiles
- Access an **analytics** dashboard with exportable reports (cases, applications, teams) via Excel export

### For Admins & Teachers
- Manage **users** across all roles
- Maintain the **case** catalog, **skills**, **badges**, and **simulators**
- Global **search** across the platform
- Role- and permission-based access control

### Platform-wide
- Public landing pages (home, about, case catalog, partner directory, achievements, "how it works")
- Email verification and role-based registration (student / partner)
- Gamification: skill points, reward rules/events, badges, and certificates
- In-app notification center

## Architecture Overview

Klust is a **server-driven monolith**. There is no separate REST API — Laravel controllers render Vue pages directly through **Inertia.js**, so routing, validation, and authorization stay on the server while the UI remains a modern single-page experience.

```
Browser (Vue 3 SPA)
        │  Inertia.js (XHR + JSON page props)
        ▼
Laravel Routes (routes/web.php)
        │
        ▼
Controllers ──► Form Requests (validation)
        │
        ▼
Service Layer (app/Services/*) ──► business logic, transactions
        │
        ▼
Eloquent Models ──► MySQL / MariaDB
```

Layered responsibilities:

- **Controllers** handle HTTP concerns only and return `Inertia::render(...)` responses or redirects with flash messages.
- **Form Requests** centralize validation and authorization.
- **Services** (`UserService`, `CaseService`, `ApplicationService`, `SimulatorService`, the `BankSimulator` services, etc.) hold business logic and wrap multi-table operations in database transactions.
- **Models** define Eloquent relationships, accessors, and scopes.

The application serves three interfaces under separate route groups and layouts:

| Interface | Route prefix | Roles | Layout |
|-----------|--------------|-------|--------|
| Admin panel | `/admin/*` | `admin`, `teacher` | `AdminLayout.vue` |
| Student portal | `/student/*` | `student` | `StudentLayout.vue` |
| Partner portal | `/partner/*` | `partner` | `PartnerLayout.vue` |
| Public site | `/*` | guest | `GuestLayout.vue` |

Authentication is handled by **Laravel Sanctum**, and authorization by **Spatie Laravel Permission** (roles: `student`, `teacher`, `partner`, `admin`).

## Technology Stack

**Backend**
- PHP 8.1+
- Laravel 10.10
- Laravel Sanctum (authentication)
- Spatie Laravel Permission (roles & permissions)
- Maatwebsite Excel (report exports)
- MySQL / MariaDB (Eloquent ORM)

**Frontend**
- Vue 3 (Composition API, `<script setup>`)
- Inertia.js 2.x
- Vite 5
- TailwindCSS 3
- PrimeVue 4 (DataTable, Dialog, Chart, etc.)
- Pinia (state management)
- Chart.js + vue-chartjs (analytics charts)
- Three.js + TresJS (3D simulator scenes)
- Ziggy (named routes in JS)

**Tooling**
- Laravel Pint (code style)
- PHPUnit 10 (testing)
- Laravel Sail (optional Docker dev environment)

## Installation

### Prerequisites

- PHP **8.1+** with `ext-dom` and `ext-pdo`
- Composer
- Node.js 18+ and npm
- MySQL or MariaDB

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/<your-org>/klust.git
cd klust

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies
npm install

# 4. Create your environment file
cp .env.example .env

# 5. Generate the application key
php artisan key:generate

# 6. Configure your database in .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 7. Run migrations and seed demo data
php artisan migrate --seed

# 8. Build front-end assets
npm run build
```

> **Note:** The seeders populate the database with a realistic demo dataset (hundreds of students, partners, cases, applications, skills, badges, simulator sessions, and notifications), which is convenient for exploring the platform.

## Local Development Setup

Run the back end and front end in parallel during development:

```bash
# Terminal 1 — Vite dev server (hot module replacement)
npm run dev

# Terminal 2 — Laravel application server
php artisan serve
```

The app will be available at the `APP_URL` configured in your `.env` (default `http://127.0.0.1:8000`).

Useful commands:

```bash
# Run the test suite
php artisan test

# Format code with Laravel Pint
./vendor/bin/pint

# Refresh the database with fresh demo data
php artisan migrate:fresh --seed
```

If you prefer containers, a [Laravel Sail](https://laravel.com/docs/sail) configuration is included.

## Usage Examples

### Rendering a page via Inertia (controller)

```php
public function index()
{
    $cases = $this->caseService->getRecommendedCases(auth()->user());

    return Inertia::render('Client/Student/Cases/Index', [
        'cases' => $cases,
    ]);
}
```

### Submitting a form from Vue (Inertia form helper)

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    case_id: null,
    team_members: [],
})

const apply = (caseId) => {
    form.post(route('student.cases.apply', { case: caseId }))
}
</script>
```

### Business logic in a service

```php
// app/Services/CaseService.php
public function createCase(array $data): CaseModel
{
    return DB::transaction(function () use ($data) {
        $case = CaseModel::create($data);
        $case->skills()->sync($data['required_skills'] ?? []);

        return $case;
    });
}
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Admin panel
│   │   ├── Auth/             # Login, register, email verification
│   │   ├── Client/
│   │   │   ├── Student/      # Student portal
│   │   │   └── Partner/      # Partner portal
│   │   └── Search/
│   ├── Requests/             # Form Request validation (Admin/Student/Partner/Auth)
│   └── Middleware/
├── Models/                   # Eloquent models (User, CaseModel, Skill, Badge, Simulator, …)
└── Services/                 # Business logic
    └── Simulators/
        └── BankSimulator/    # Client generation, dialogue, scoring, calculators, evaluation

resources/js/
├── Pages/                    # Inertia pages (Admin, Auth, Client, Guest, Errors, Notifications)
├── Layouts/                  # AdminLayout, StudentLayout, PartnerLayout, GuestLayout
├── Components/               # Reusable Vue components (Shared, UI)
└── Composables/

database/
├── migrations/               # Schema definitions
├── seeders/                  # Demo data seeders
└── factories/

routes/
└── web.php                   # All application routes (no api.php — this is not a REST API)

docs/
├── simulator/                # Bank simulator design, formulas, and implementation roadmap
└── testing/                  # Manual E2E test cases and report templates
```

## Roadmap

Klust is an early-stage but feature-rich project. Planned directions include expanding automated test coverage, completing the simulator framework, internationalization, and improved partner/student collaboration tools. See [ROADMAP.md](ROADMAP.md) for the full milestone breakdown.

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for setup instructions, coding standards, and the pull-request workflow before submitting changes.

A few highlights:

- Follow **PSR-12** and add `declare(strict_types=1);` to new PHP files.
- Use Vue 3 **Composition API** with `<script setup>`.
- Keep controllers thin — put business logic in **services**.
- Run `./vendor/bin/pint` and `php artisan test` before opening a pull request.

## License

This project is open-source software licensed under the [MIT License](LICENSE).
