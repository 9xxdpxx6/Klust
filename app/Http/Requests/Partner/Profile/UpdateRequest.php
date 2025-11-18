<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Импортируем для Rule::unique

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный партнер и он редактирует свой профиль.
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
        $user = auth()->user(); // Получаем текущего пользователя для правила unique

        return [
            // --- Поля из таблицы 'users' ---
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id), // Исключаем текущего пользователя из проверки уникальности
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // Опционален, если есть - должен быть подтвержден

            // --- Поля из таблицы 'partner_profiles' ---
            'company_name' => ['required', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:12'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],

            // Логотип компании по правилам для логотипов
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:5120', // 5MB
            ],
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
            // Сообщения для полей пользователя
            'name.required' => 'Имя обязательно для заполнения.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',

            // Сообщения для полей профиля партнера
            'company_name.required' => 'Название компании обязательно для заполнения.',
            'inn.max' => 'ИНН не должен превышать 12 символов.',
            'website.url' => 'Поле "Сайт" должно содержать корректный URL-адрес.',
            'contact_person.max' => 'Имя контактного лица не должно превышать 255 символов.',
            'contact_phone.max' => 'Номер телефона не должен превышать 20 символов.',
            'logo.image' => 'Файл логотипа должен быть изображением.',
            'logo.mimes' => 'Логотип должен быть в формате: jpeg, png, jpg, gif или svg.',
            'logo.max' => 'Максимальный размер логотипа не должен превышать 5 МБ.',
        ];
    }
}
