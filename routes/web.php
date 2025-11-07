<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\Partner\AnalyticsController;
use App\Http\Controllers\Client\Partner\CasesController;
use App\Http\Controllers\Client\Partner\DashboardController;
use App\Http\Controllers\Client\Partner\ProfileController;
use App\Http\Controllers\Client\Partner\TeamController;
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

        if (!$user) {
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

    // Админка (будет дополнено позже)
    Route::prefix('admin')->middleware('role:admin|teacher')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');
    });

    // Студент (будет дополнено позже)
    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Client/Student/Dashboard');
        })->name('dashboard');
    });

    // Партнер
    Route::prefix('partner')->middleware('role:partner')->name('partner.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        
        // Cases
        Route::get('/cases', [CasesController::class, 'index'])->name('cases.index');
        Route::get('/cases/create', [CasesController::class, 'create'])->name('cases.create');
        Route::post('/cases', [CasesController::class, 'store'])->name('cases.store');
        Route::get('/cases/{case}', [CasesController::class, 'show'])->name('cases.show');
        Route::get('/cases/{case}/edit', [CasesController::class, 'edit'])->name('cases.edit');
        Route::put('/cases/{case}', [CasesController::class, 'update'])->name('cases.update');
        Route::post('/cases/{case}/archive', [CasesController::class, 'archive'])->name('cases.archive');
        Route::get('/cases/{case}/applications', [CasesController::class, 'applications'])->name('cases.applications');
        Route::post('/cases/{case}/applications/{application}/approve', [CasesController::class, 'approve'])->name('cases.applications.approve');
        Route::post('/cases/{case}/applications/{application}/reject', [CasesController::class, 'reject'])->name('cases.applications.reject');
        
        // Teams
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/{application}', [TeamController::class, 'show'])->name('teams.show');
        
        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

Route::prefix('admin')->middleware(['auth', 'role:admin|teacher'])->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    // Маршруты пользователей
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
});

Route::get('/', function () {
    return view('welcome');
});

