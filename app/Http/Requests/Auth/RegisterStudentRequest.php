<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentRequest extends FormRequest
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
        return [
            'kubgtu_id' => ['nullable', 'string', 'max:255', 'unique:users,kubgtu_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'course' => ['required', 'integer', 'between:1,6'],

            // Student profile fields
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
            'group' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kubgtu_id.unique' => 'Пользователь с таким ID КУБГТУ уже существует.',
            'kubgtu_id.max' => 'ID КУБГТУ не должен превышать 255 символов.',
            'name.required' => 'Имя обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.regex' => 'Email должен быть корректным адресом электронной почты (например, user@example.com).',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'password.required' => 'Пароль обязателен для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'course.required' => 'Курс обязателен для заполнения.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'faculty_id.exists' => 'Выбранный факультет не существует.',
            'group.max' => 'Название группы не должно превышать 255 символов.',
            'specialization.max' => 'Название специализации не должно превышать 255 символов.',
        ];
    }
}
