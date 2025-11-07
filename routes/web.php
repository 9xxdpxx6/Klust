<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\CaseController;
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
//    Route::prefix('admin')->middleware('role:admin|teacher')->name('admin.')->group(function () {
//        Route::get('/dashboard', function () {
//            return Inertia::render('Admin/Dashboard');
//        })->name('dashboard');
//
//        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
//        Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
//        Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
//        Route::post('/users', [UsersController::class, 'store'])->name('users.store');
//    });

    // Студент (будет дополнено позже)
    Route::prefix('student')->middleware('role:student')->name('student.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Client/Student/Dashboard');
        })->name('dashboard');
    });

    // Партнер (будет дополнено позже)
    Route::prefix('partner')->middleware('role:partner')->name('partner.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Client/Partner/Dashboard');
        })->name('dashboard');
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

    Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
    Route::get('/cases/{case}', [CaseController::class, 'show'])->name('cases.show'); // Добавляем этот маршрут

});

Route::get('/', function () {
    return view('welcome');
});

