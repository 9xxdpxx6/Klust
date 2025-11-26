<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPartnerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                'unique:users,email',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Partner profile fields
            'company_name' => ['required', 'string', 'max:255'],
            'inn' => ['nullable', 'string', 'max:12'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'description' => ['nullable', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],

            // Logo
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
            'name.required' => 'Имя обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.regex' => 'Email должен быть корректным адресом электронной почты (например, user@example.com).',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'password.required' => 'Пароль обязателен для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'company_name.required' => 'Название компании обязательно для заполнения.',
            'company_name.max' => 'Название компании не должно превышать 255 символов.',
            'inn.max' => 'ИНН не должен превышать 12 символов.',
            'website.url' => 'Поле "Сайт" должно содержать корректный URL-адрес.',
            'contact_person.required' => 'Контактное лицо обязательно для заполнения.',
            'contact_person.max' => 'Имя контактного лица не должно превышать 255 символов.',
            'contact_phone.max' => 'Номер телефона не должен превышать 20 символов.',
            'logo.image' => 'Файл логотипа должен быть изображением.',
            'logo.mimes' => 'Логотип должен быть в формате: jpeg, png, jpg, gif или svg.',
            'logo.max' => 'Максимальный размер логотипа не должен превышать 5 МБ.',
        ];
    }
}
