<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        // Определяем роль пользователя
        $isTeacher = false;
        $isStudent = false;
        $isPartner = false;
        
        if ($user && $user->roles) {
            $roleNames = $user->roles->pluck('name')->toArray();
            $isTeacher = in_array('teacher', $roleNames);
            $isStudent = in_array('student', $roleNames);
            $isPartner = in_array('partner', $roleNames);
        }
        
        // Базовые правила для всех ролей
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'current_password' => [
                'nullable',
                'required_with:password',
                'string',
                'current_password',
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
            ],
        ];
        
        // Поля для преподавателя
        if ($isTeacher) {
            $rules['department'] = ['nullable', 'string', 'max:255'];
            $rules['position'] = ['nullable', 'string', 'max:255'];
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
        }
        // Поля для студента
        elseif ($isStudent) {
            $rules['faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
            $rules['group'] = ['nullable', 'string', 'max:255'];
            $rules['specialization'] = ['nullable', 'string', 'max:255'];
            $rules['phone'] = ['nullable', 'string', 'max:20'];
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
            $rules['course'] = ['nullable', 'integer', 'between:1,6'];
        }
        // Поля для партнера
        elseif ($isPartner) {
            $rules['company_name'] = ['nullable', 'string', 'max:255'];
            $rules['inn'] = ['nullable', 'string', 'max:12'];
            $rules['address'] = ['nullable', 'string'];
            $rules['website'] = ['nullable', 'url', 'max:255'];
            $rules['description'] = ['nullable', 'string'];
            $rules['contact_person'] = ['nullable', 'string', 'max:255'];
            $rules['contact_phone'] = ['nullable', 'string', 'max:20'];
        }
        // Для админа - только базовые поля (name, email, avatar, password)
        
        return $rules;
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.regex' => 'Email должен быть корректным адресом электронной почты.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'current_password.required_with' => 'Для смены пароля необходимо ввести текущий пароль.',
            'current_password.current_password' => 'Указан неверный текущий пароль.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
            'phone.max' => 'Номер телефона не должен превышать 20 символов.',
            'bio.max' => 'Биография не должна превышать 1000 символов.',
        ];
    }
}
