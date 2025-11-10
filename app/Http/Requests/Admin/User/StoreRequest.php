<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Например, только администратор может создавать пользователей.
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:student, teacher, partner, admin'],

            // Поля профиля, зависящие от роли
            // kubgtu_id обязателен только если role === 'student'
            'kubgtu_id' => [
                'required_if:role,student',
                'string',
                'unique:users,kubgtu_id' // Уникален для всех пользователей
            ],
            // course обязателен только если role === 'student'
            'course' => [
                'required_if:role,student',
                'integer',
                'between:1,6' // Предположим, что курсы от 1 до 6
            ],

            // Аватар по общим правилам
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
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
            'name.required' => 'Имя пользователя обязательно для заполнения.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.required' => 'Пароль обязателен для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'role.required' => 'Необходимо указать роль пользователя.',
            'role.in' => 'Выбрана некорректная роль.',
            'kubgtu_id.required_if' => 'Поле "ID КУБГТУ" обязательно для студентов.',
            'kubgtu_id.unique' => 'Пользователь с таким ID КУБГТУ уже существует.',
            'course.required_if' => 'Поле "Курс" обязательно для студентов.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
        ];
    }
}
