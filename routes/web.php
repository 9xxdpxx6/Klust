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
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('cases', CasesController::class);
        Route::post('/cases/{case}/archive', [CasesController::class, 'archive'])->name('cases.archive');
        Route::get('/cases/{case}/applications', [CasesController::class, 'applications'])->name('cases.applications');
        Route::post('/cases/{case}/applications/{application}/approve', [CasesController::class, 'approve'])->name('cases.applications.approve');
        Route::post('/cases/{case}/applications/{application}/reject', [CasesController::class, 'reject'])->name('cases.applications.reject');
        Route::resource('team', TeamController::class);
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });
});

Route::get('/', function () {
    return view('welcome');
});
