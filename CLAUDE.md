# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Klust** is a Laravel 10.10 + Vue 3 + Inertia.js monolithic web application for managing educational case-based learning. It connects students with industry partners for real-world project experience.

### Tech Stack
- **Backend**: Laravel 10.10, PHP 8.1+
- **Frontend**: Vue 3 (Composition API), Inertia.js, Vite
- **UI**: TailwindCSS, PrimeVue
- **State**: Pinia
- **Auth**: Laravel Sanctum + Spatie Laravel Permission
- **Database**: MySQL/MariaDB (via Eloquent ORM)

## Development Commands

**IMPORTANT**: Do NOT run `npm run dev` or `php artisan serve` - these development servers are not available in this environment. Only run final build scripts like `npm run build` and artisan commands that don't start servers.

### Setup & Installation
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seed
php artisan migrate --seed
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Admin/CaseControllerTest.php

# Run tests with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Fix specific file
./vendor/bin/pint app/Http/Controllers/Admin/CaseController.php
```

### Building for Production
```bash
# Build frontend assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database
```bash
# Create migration
php artisan make:migration create_table_name

# Create model with migration, factory, seeder
php artisan make:model ModelName -mfs

# Refresh database (drops all tables)
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback
```

## Architecture

### Multi-Interface System

The application serves 3 distinct user interfaces with role-based access:

1. **Admin Panel** (`/admin/*`) - For KubGTU staff (roles: `admin`, `teacher`)
2. **Student Portal** (`/student/*`) - For students (role: `student`)
3. **Partner Portal** (`/partner/*`) - For industry partners (role: `partner`)

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin panel controllers
│   │   ├── Auth/           # Authentication controllers
│   │   └── Client/         # Student & Partner controllers
│   │       ├── Student/
│   │       └── Partner/
│   ├── Requests/           # Form Request validation classes
│   │   ├── Admin/
│   │   ├── Auth/
│   │   ├── Student/
│   │   └── Partner/
│   └── Middleware/
├── Models/                 # Eloquent models
└── Services/               # Business logic services

resources/js/
├── Pages/                  # Inertia pages (one per route)
│   ├── Admin/
│   ├── Auth/
│   └── Client/
│       ├── Student/
│       └── Partner/
├── Layouts/                # Reusable layouts
│   ├── AdminLayout.vue
│   ├── StudentLayout.vue
│   ├── PartnerLayout.vue
│   └── GuestLayout.vue
├── Components/             # Reusable Vue components
│   ├── Shared/
│   └── UI/
└── Composables/            # Vue composables

routes/
└── web.php                 # All routes (no api.php - this is not an API)
```

### Service Layer Pattern

Business logic is extracted into Service classes to keep controllers thin:

- **Controllers**: Handle HTTP requests/responses only
- **Services**: Contain business logic, transactions, and complex operations
- **Form Requests**: Handle validation

Example service classes:
- `app/Services/UserService.php`
- `app/Services/CaseService.php`
- `app/Services/ApplicationService.php`
- `app/Services/StudentService.php`
- `app/Services/PartnerService.php`

### Key Models & Relationships

Core models (see `app/Models/`):
- **User** - Base user model with polymorphic profiles
- **StudentProfile, PartnerProfile, TeacherProfile** - User profiles
- **CaseModel** - Industry cases/projects
- **CaseApplication** - Student team applications to cases
- **CaseTeamMember** - Team member records
- **Skill** - Skills (many-to-many with Users and Cases)
- **Badge** - Achievement badges
- **Simulator** - Educational simulators
- **SimulatorSession** - Simulator completion records

Key relationships:
- User → Profiles (hasOne, polymorphic via StudentProfile, PartnerProfile, TeacherProfile)
- User → Skills (belongsToMany via `user_skills` pivot)
- User → Badges (belongsToMany via `user_badges` pivot)
- CaseModel → Partner (belongsTo)
- CaseModel → Skills (belongsToMany via `case_skills` pivot)
- CaseApplication → CaseModel (belongsTo)
- CaseApplication → Leader (User, belongsTo)
- CaseApplication → TeamMembers (hasMany CaseTeamMember)

## Key Development Principles

### Code Quality Standards
- Write concise, technical responses with accurate PHP and Vue.js examples
- Follow **SOLID principles** in object-oriented programming
- Favor **iteration and modularization** over code duplication
- Use **descriptive and meaningful names** for variables, methods, and files
- Adhere to Laravel's directory structure conventions
- Prioritize **dependency injection** and **service container** usage

### PHP Development
- Use **PHP 8.1+** features: readonly properties, enums, match expressions, union types
- Apply **strict typing**: `declare(strict_types=1);` at the top of every PHP file
- Follow **PSR-12** coding standards
- Use Laravel's built-in helpers: `Str::`, `Arr::`, `Collection`, etc.
- Implement proper **error handling**:
  - Use Laravel's exception handling (`app/Exceptions/Handler.php`)
  - Create custom exceptions when needed (e.g., `BusinessLogicException`)
  - Apply `try/catch` blocks for predictable errors
  - Never suppress exceptions silently

### Laravel Architecture
- Stick to **MVC architecture** with Service layer
- Use **Eloquent ORM** for all database operations
- Manage schema with **migrations**, **seeders**, and **factories**
- Apply **repository pattern** for complex data access (when needed)
- Use **service classes** for business logic (always)
- Secure endpoints with **CSRF protection** (automatic with Inertia)
- Use **events**, **listeners**, and **jobs** for async/background tasks
- Implement **queues** for long-running operations
- Apply **caching** (Redis, file, database) for performance optimization
- Use **transactions** (`DB::transaction()`) for multi-table operations
- Apply **database indexes** for frequently queried columns

### Vue.js Development
- Use **Vue 3 Composition API** exclusively with `<script setup>` syntax
- Organize components logically:
  - **Pages** → `resources/js/Pages/` (Inertia page components)
  - **Components** → `resources/js/Components/` (reusable UI components)
  - **Layouts** → `resources/js/Layouts/` (layout wrappers)
  - **Composables** → `resources/js/Composables/` (reusable logic)
- Use **TailwindCSS** for styling and responsive design
- Use **PrimeVue** components for complex UI:
  - DataTable for data tables
  - Dialog for modals
  - Calendar for date pickers
  - FileUpload for file uploads
  - Chart for data visualization
- Handle form submissions with `@inertiajs/vue3` form helpers
- Create reusable components for flash messages and modals
- Use **Pinia** for global state management when needed
- Access route helpers via **Ziggy**: `route('admin.dashboard')`

## Development Guidelines

### Inertia.js Usage

This is NOT a REST API - do NOT build separate API endpoints. Use Inertia.js to connect Laravel backend routes directly to Vue 3 pages.

**Controller Actions:**
```php
// Render Inertia page with props
return Inertia::render('Admin/Dashboard', [
    'statistics' => $stats,
    'user' => Auth::user(),
]);

// Actions redirect with flash messages
return redirect()->route('admin.cases.index')
    ->with('success', 'Case created successfully');
```

**Shared Props:**
Define shared data (auth user, flash messages) in `app/Http/Middleware/HandleInertiaRequests.php`:
```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ]);
}
```

**Route Structure:**
- Keep all routes in `routes/web.php`
- DO NOT use `routes/api.php` unless building an actual external API
- Use standard Laravel web middleware

### Form Validation

Use Form Request classes for all validation (never validate in controllers):

```php
// Controller
public function store(Admin\Case\StoreRequest $request): RedirectResponse
{
    $case = $this->caseService->createCase($request->validated());
    return redirect()->route('admin.cases.show', $case);
}
```

Form Request location pattern: `app/Http/Requests/{Role}/{Resource}/{Action}Request.php`

### Service Pattern

Delegate business logic to services:

```php
// In controller
public function store(StoreRequest $request)
{
    // Business logic in service
    $case = $this->caseService->createCase($request->validated());

    // Controller only handles HTTP concerns
    return redirect()->route('admin.cases.show', $case)
        ->with('success', 'Case created');
}

// In service (app/Services/CaseService.php)
public function createCase(array $data): CaseModel
{
    return DB::transaction(function () use ($data) {
        $case = CaseModel::create($data);
        $case->skills()->sync($data['required_skills'] ?? []);
        return $case;
    });
}
```

### Authentication & Authorization

- **Authentication**: Laravel Sanctum
- **Role Management**: Spatie Laravel Permission package
- **Middleware**: Apply `auth` and `role:admin|teacher` middleware to routes
- **Dashboard Routing**: Different dashboards per role (see `routes/web.php`)

### Vue Components

Use Vue 3 Composition API with `<script setup>`:

```vue
<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
    case: Object,
    skills: Array
})

const form = useForm({
    title: '',
    description: '',
    required_skills: []
})

const submit = () => {
    form.post(route('partner.cases.store'), {
        onSuccess: () => {
            // Form automatically redirects on success
        },
        onError: (errors) => {
            // Errors automatically available in form.errors
        }
    })
}
</script>

<template>
    <form @submit.prevent="submit">
        <div>
            <label>Title</label>
            <input v-model="form.title" type="text" />
            <div v-if="form.errors.title">{{ form.errors.title }}</div>
        </div>

        <button type="submit" :disabled="form.processing">
            Submit
        </button>
    </form>
</template>
```

**Component Organization:**
- Keep components focused and single-responsibility
- Extract reusable UI components to `Components/UI/`
- Use layouts for consistent page structure
- Access shared props via `$page.props.auth`, `$page.props.flash`

### Route Naming Convention

Follow **RESTful routing** conventions in `routes/web.php`:

```
admin.users.index       # GET /admin/users
admin.users.show        # GET /admin/users/{user}
admin.users.create      # GET /admin/users/create
admin.users.store       # POST /admin/users
admin.users.edit        # GET /admin/users/{user}/edit
admin.users.update      # PUT/PATCH /admin/users/{user}
admin.users.destroy     # DELETE /admin/users/{user}

student.cases.index     # GET /student/cases
student.cases.show      # GET /student/cases/{case}
student.cases.apply     # POST /student/cases/{case}/apply

partner.cases.index     # GET /partner/cases
partner.cases.store     # POST /partner/cases
```

**Route Organization:**
- Group routes by role using `Route::middleware()` and `Route::prefix()`
- Use resource routes when possible: `Route::resource('users', UserController::class)`
- Custom actions should be descriptive: `cases/{case}/apply`, `cases/{case}/archive`
- Always name routes for use with Ziggy

**In Vue components:**
```javascript
// Use Ziggy for named routes
route('admin.dashboard')
route('admin.users.show', user.id)
route('student.cases.apply', { case: caseId })
```

### Database Conventions

- **Migrations**: Use descriptive names (`create_case_skills_table`)
- **Pivot tables**: Singular model names in alphabetical order (`case_skills`, `user_skills`)
- **Enums**: Use string enums in migrations (`status` column: draft, active, completed, archived)
- **Soft deletes**: Applied to User and CaseModel

### Code Style

- PHP: Follow **PSR-12** and Laravel conventions
- Use `declare(strict_types=1);` in all new PHP files
- Type hint all method parameters and return types
- Use Laravel's built-in helpers: `Str::`, `Arr::`, etc.
- Vue: Use `<script setup>` syntax, no Options API

## Important Project Details

### Case Application Workflow

1. Student creates application as team leader
2. Can add team members (up to `required_team_size - 1`)
3. Partner reviews application
4. Partner approves/rejects with optional comment
5. If approved, team members can access case workspace

### Team Size Rules

- `required_team_size` on CaseModel specifies total team size
- Leader (applicant) counts as 1
- Can add up to `required_team_size - 1` additional members
- Maximum team size: 10 people

### Case Statuses

- **draft**: Created but not visible to students
- **active**: Published and accepting applications
- **completed**: Finished (admin only)
- **archived**: No longer active (admin/partner)

### Application Statuses

- **pending**: Awaiting partner review
- **accepted**: Approved by partner
- **rejected**: Rejected by partner

### Skill & Badge System

- Skills have levels and points tracked in `user_skills` pivot
- Badges auto-assigned when student reaches `required_points`
- Simulator completion awards skill points

## Best Practices

### General Development
- Keep logic modular and decoupled
- Use **repository + service layers** for maintainability
- Leverage **Eloquent relationships**, **mutators**, and **accessors**
- Use **transactions** for operations affecting multiple tables
- Apply **database indexes** for frequently queried columns
- Optimize with **eager loading** to prevent N+1 query problems

### Security
- Secure all routes with appropriate `auth` and `role` middleware
- CSRF protection is automatic with Inertia (use web middleware)
- Validate all user input through Form Request classes
- Never trust client-side data - always validate on server
- Use Spatie Permission for role-based access control
- Check resource ownership in controllers (e.g., partner can only edit own cases)

### Performance
- Use **eager loading** (`with()`) to prevent N+1 queries
- Cache expensive queries (statistics, dashboard data)
- Use `chunk()` or `cursor()` for large datasets
- Queue long-running operations (emails, notifications, reports)
- Optimize database queries with `select()` to load only needed columns
- Use database indexes on foreign keys and frequently queried columns

### Testing
- Write feature tests for all critical user flows
- Test Form Request validation rules
- Test service layer business logic
- Use factories for test data generation
- Test middleware and authorization logic

### Code Organization
- Controllers: HTTP layer only (request → service → response)
- Services: Business logic, transactions, complex operations
- Repositories: Optional abstraction for complex queries
- Models: Relationships, accessors, mutators, scopes
- Form Requests: Validation rules and authorization

### Vite Integration
- Use Vite for asset bundling (configured in `vite.config.js`)
- Import assets using `@/` alias pointing to `resources/js/`
- Hot module replacement works with Inertia for fast development
- Run `npm run build` before deployment to compile production assets

## Reference Files

Detailed specifications are in project root:
- `ARCHITECTURE_PLAN.md` - Complete architecture overview

## Common Patterns

### Creating a new admin resource:

1. Create Form Requests: `StoreRequest.php`, `UpdateRequest.php`
2. Create Service: `app/Services/ResourceService.php`
3. Create Controller: `app/Http/Controllers/Admin/ResourceController.php`
4. Create Vue pages in `resources/js/Pages/Admin/Resource/`
5. Add routes in `routes/web.php` under admin group
6. Update navigation in `resources/js/Layouts/AdminLayout.vue`

### Adding a new student feature:

1. Create Form Request if needed
2. Add method to existing service or create new one
3. Add controller method in `Client/Student/`
4. Create Vue page in `Pages/Client/Student/`
5. Add route under student group
6. Update `StudentLayout.vue` navigation

### Working with Inertia shared data

Shared props (available on all pages) are configured in `app/Http/Middleware/HandleInertiaRequests.php`.

**Access in Vue components:**
```vue
<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const flash = computed(() => page.props.flash)
</script>

<template>
    <div v-if="flash.success">{{ flash.success }}</div>
    <div>Welcome, {{ user.name }}</div>
</template>
```

**Or use template directly:**
```vue
<template>
    <div v-if="$page.props.flash.success">
        {{ $page.props.flash.success }}
    </div>
    <div>Welcome, {{ $page.props.auth.user.name }}</div>
</template>
```

## Standard Development Workflow

When adding a new feature, follow this sequence:

1. **Define the route** in `routes/web.php`
   ```php
   Route::get('/admin/badges', [Admin\BadgeController::class, 'index'])
       ->name('admin.badges.index');
   ```

2. **Create controller method** returning `Inertia::render()`
   ```php
   public function index()
   {
       $badges = $this->badgeService->getAllBadges();
       return Inertia::render('Admin/Badges/Index', [
           'badges' => $badges
       ]);
   }
   ```

3. **Build Vue page** in `resources/js/Pages/` matching the Inertia render path
   ```vue
   <!-- resources/js/Pages/Admin/Badges/Index.vue -->
   <script setup>
   const props = defineProps({
       badges: Array
   })
   </script>
   ```

4. **Use shared layouts** for consistent structure (`AdminLayout.vue`, `StudentLayout.vue`, etc.)

5. **Extract reusable components** to `resources/js/Components/`

6. **Test the feature** with PHPUnit feature tests

7. **Document changes** - update architecture docs if adding new models or major features

8. **Commit schema and dependency changes** - always commit migration files, updated `composer.json`, `package.json`, and lock files
