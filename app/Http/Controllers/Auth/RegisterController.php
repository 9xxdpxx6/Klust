<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterPartnerRequest;
use App\Http\Requests\Auth\RegisterStudentRequest;
use App\Models\PartnerProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                'kubgtu_id' => $request->kubgtu_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'course' => $request->course,
            ]);

            // Создание профиля студента
            StudentProfile::create([
                'user_id' => $user->id,
            ]);

            // Назначение роли студента
            $user->assignRole('student');

            DB::commit();

            // Автоматический вход после регистрации
            Auth::login($user);

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
            ]);

            // Назначение роли партнера
            $user->assignRole('partner');

            DB::commit();

            // Автоматический вход после регистрации
            Auth::login($user);

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

