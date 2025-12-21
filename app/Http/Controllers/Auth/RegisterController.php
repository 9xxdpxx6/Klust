<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterPartnerRequest;
use App\Http\Requests\Auth\RegisterStudentRequest;
use App\Mail\WelcomePartnerMail;
use App\Mail\WelcomeStudentMail;
use App\Models\PartnerProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function show(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Обработать регистрацию студента
     */
    public function registerStudent(RegisterStudentRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'kubgtu_id' => $request->kubgtu_id ?: null,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Создание профиля студента
            StudentProfile::create([
                'user_id' => $user->id,
                'course' => $request->course,
            ]);

            // Назначение роли студента
            $user->assignRole('student');

            DB::commit();

            // Вызываем событие Registered для отправки письма верификации
            // Важно: событие должно быть вызвано ДО входа пользователя
            event(new Registered($user));
            
            // Также отправляем письмо напрямую на случай, если событие не сработало
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::error('Failed to send verification email to student', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // Отправить welcome email (без очереди)
            try {
                Mail::to($user->email)->send(new WelcomeStudentMail($user));
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем регистрацию
                Log::error('Failed to send welcome email to student: '.$e->getMessage());
            }

            // Автоматический вход после регистрации
            Auth::login($user);

            // Перенаправляем на страницу верификации, если email не подтвержден
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('success', 'Регистрация успешно завершена! Пожалуйста, подтвердите ваш email адрес.');
            }

            return redirect()->route('student.dashboard')
                ->with('success', 'Регистрация успешно завершена! Добро пожаловать!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Произошла ошибка при регистрации. Попробуйте позже.',
            ]);
        }
    }

    /**
     * Обработать регистрацию партнера
     */
    public function registerPartner(RegisterPartnerRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->contact_person,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Создание профиля партнера
            PartnerProfile::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'description' => $request->description ?? '',
                'is_active' => true,
            ]);

            // Назначение роли партнера
            $user->assignRole('partner');

            DB::commit();

            // Вызываем событие Registered для отправки письма верификации
            // Важно: событие должно быть вызвано ДО входа пользователя
            event(new Registered($user));
            
            // Также отправляем письмо напрямую на случай, если событие не сработало
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::error('Failed to send verification email to partner', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // Отправить welcome email (без очереди)
            try {
                Mail::to($user->email)->send(new WelcomePartnerMail($user));
            } catch (\Exception $e) {
                // Логируем ошибку, но не прерываем регистрацию
                Log::error('Failed to send welcome email to partner: '.$e->getMessage());
            }

            // Автоматический вход после регистрации
            Auth::login($user);

            // Перенаправляем на страницу верификации, если email не подтвержден
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('success', 'Регистрация успешно завершена! Пожалуйста, подтвердите ваш email адрес.');
            }

            return redirect()->route('partner.dashboard')
                ->with('success', 'Регистрация успешно завершена! Добро пожаловать!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Произошла ошибка при регистрации. Попробуйте позже.',
            ]);
        }
    }

    /**
     * Универсальный метод регистрации (определяет тип по типу запроса)
     *
     * @deprecated Используйте registerStudent() или registerPartner() напрямую
     */
    public function register(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'student');

        if ($type === 'partner') {
            $partnerRequest = RegisterPartnerRequest::createFrom($request);
            $partnerRequest->setContainer(app());
            $partnerRequest->validateResolved();

            return $this->registerPartner($partnerRequest);
        }

        $studentRequest = RegisterStudentRequest::createFrom($request);
        $studentRequest->setContainer(app());
        $studentRequest->validateResolved();

        return $this->registerStudent($studentRequest);
    }
}
