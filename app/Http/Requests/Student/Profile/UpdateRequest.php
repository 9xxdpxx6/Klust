<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный студент и он редактирует свой профиль.
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
        $userId = auth()->id(); // Получаем ID текущего пользователя для правила unique

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                Rule::unique('users')->ignore($userId), // Исключаем текущего пользователя из проверки уникальности
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'course' => ['nullable', 'integer', 'min:1', 'max:6'], // Курсы от 1 до 6

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
            ],

            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
            'group' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
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
            'name.required' => 'Имя обязательно для заполнения.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.regex' => 'Email должен быть корректным адресом электронной почты (например, user@example.com).',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'course.min' => 'Курс должен быть не менее 1.',
            'course.max' => 'Курс должен быть не более 6.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
            'faculty_id.exists' => 'Выбранный факультет не существует.',
            'group.max' => 'Название группы не должно превышать 255 символов.',
            'specialization.max' => 'Название специализации не должно превышать 255 символов.',
            'bio.max' => 'Биография не должна превышать 1000 символов.',
        ];
    }
}
