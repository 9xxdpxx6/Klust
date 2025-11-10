<?php

use App\Http\Controllers\Admin\CaseController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\Partner\AnalyticsController;
use App\Http\Controllers\Client\Partner\CasesController as PartnerCasesController;
use App\Http\Controllers\Client\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\Client\Partner\ProfileController as PartnerProfileController;
use App\Http\Controllers\Client\Partner\TeamController;
use App\Http\Controllers\Client\Student\BadgesController;
use App\Http\Controllers\Client\Student\CasesController as StudentCasesController;
use App\Http\Controllers\Client\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Client\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Client\Student\SimulatorsController;
use App\Http\Controllers\Client\Student\SkillsController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Гость
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/register/student', [RegisterController::class, 'registerStudent'])->name('register.student');
    Route::post('/register/partner', [RegisterController::class, 'registerPartner'])->name('register.partner');
});

// Авторизованные
Route::middleware('auth')->group(function () {
    // Редирект на dashboard по роли
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole(['admin', 'teacher'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('partner')) {
            return redirect()->route('partner.dashboard');
        }

        return redirect()->route('login');
    })->name('dashboard');

    // Выход
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Студент
    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        // Cases - Catalog
        Route::get('/cases', [StudentCasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/my', [StudentCasesController::class, 'myCases'])->name('cases.my');
        Route::get('/cases/{case}', [StudentCasesController::class, 'show'])->name('cases.show');

        // Applications
        Route::post('/cases/{case}/apply', [StudentCasesController::class, 'apply'])->name('cases.apply');
        Route::delete('/applications/{application}', [StudentCasesController::class, 'withdraw'])->name('applications.withdraw');

        // Team
        Route::get('/team/{application}', [StudentCasesController::class, 'team'])->name('team.show');
        Route::post('/team/{application}/members', [StudentCasesController::class, 'addTeamMember'])->name('team.addMember');

        // Profile
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');

        // Skills & Badges
        Route::get('/skills', [SkillsController::class, 'index'])->name('skills.index');
        Route::get('/badges', [BadgesController::class, 'index'])->name('badges.index');

        // Simulators
        Route::get('/simulators', [SimulatorsController::class, 'index'])->name('simulators.index');
        Route::post('/simulators/{simulator}/start', [SimulatorsController::class, 'start'])->name('simulators.start');
        Route::get('/simulators/session/{session}', [SimulatorsController::class, 'session'])->name('simulators.session');
        Route::post('/simulators/session/{session}/complete', [SimulatorsController::class, 'complete'])->name('simulators.complete');
    });

    // Партнер
    Route::prefix('partner')->middleware('role:partner')->name('partner.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', [PartnerProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [PartnerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [PartnerProfileController::class, 'update'])->name('profile.update');

        // Cases
        Route::get('/cases', [PartnerCasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/create', [PartnerCasesController::class, 'create'])->name('cases.create');
        Route::post('/cases', [PartnerCasesController::class, 'store'])->name('cases.store');
        Route::get('/cases/{case}', [PartnerCasesController::class, 'show'])->name('cases.show');
        Route::get('/cases/{case}/edit', [PartnerCasesController::class, 'edit'])->name('cases.edit');
        Route::put('/cases/{case}', [PartnerCasesController::class, 'update'])->name('cases.update');
        Route::post('/cases/{case}/archive', [PartnerCasesController::class, 'archive'])->name('cases.archive');
        Route::get('/cases/{case}/applications', [PartnerCasesController::class, 'applications'])->name('cases.applications');
        Route::post('/cases/{case}/applications/{application}/approve', [PartnerCasesController::class, 'approve'])->name('cases.applications.approve');
        Route::post('/cases/{case}/applications/{application}/reject', [PartnerCasesController::class, 'reject'])->name('cases.applications.reject');

        // Teams
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/{application}', [TeamController::class, 'show'])->name('teams.show');

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SimulatorController;

Route::prefix('admin')->middleware(['auth', 'role:admin|teacher'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Маршруты пользователей
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
    Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
    Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{case}', [CaseController::class, 'show'])->name('cases.show');
    Route::get('/cases/{case}/edit', [CaseController::class, 'edit'])->name('cases.edit');
    Route::put('/cases/{case}', [CaseController::class, 'update'])->name('cases.update');
    Route::delete('/cases/{case}', [CaseController::class, 'destroy'])->name('cases.destroy');

    // Skills
    Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
    Route::post('/skills', [SkillController::class, 'store'])->name('skills.store');
    Route::put('/skills/{skill}', [SkillController::class, 'update'])->name('skills.update');
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy'])->name('skills.destroy');

    // Badges
    Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::post('/badges', [BadgeController::class, 'store'])->name('badges.store');
    Route::put('/badges/{badge}', [BadgeController::class, 'update'])->name('badges.update');
    Route::delete('/badges/{badge}', [BadgeController::class, 'destroy'])->name('badges.destroy');

    // Simulators
    Route::get('/simulators', [SimulatorController::class, 'index'])->name('simulators.index');
    Route::post('/simulators', [SimulatorController::class, 'store'])->name('simulators.store');
    Route::put('/simulators/{simulator}', [SimulatorController::class, 'update'])->name('simulators.update');
    Route::delete('/simulators/{simulator}', [SimulatorController::class, 'destroy'])->name('simulators.destroy');
});

Route::get('/', function () {
    return view('welcome');
});
