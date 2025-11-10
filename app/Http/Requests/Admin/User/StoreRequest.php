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
            'role' => ['required', 'string', 'in:student,teacher,partner,admin'],

            // Поля из основной таблицы users, зависящие от роли
            'kubgtu_id' => [
                'required_if:role,student',
                'string',
                'max:255',
                'unique:users,kubgtu_id' // Уникален для всех пользователей
            ],
            'course' => [
                'required_if:role,student',
                'integer',
                'between:1,6' // Курсы от 1 до 6
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],

            // Поля из student_profiles (только для студентов)
            'faculty_id' => [
                'required_if:role,student',
                'nullable',
                'integer',
                'exists:faculties,id'
            ],
            'group' => [
                'required_if:role,student',
                'nullable',
                'string',
                'max:255'
            ],
            'specialization' => [
                'nullable',
                'string',
                'max:255'
            ],
            'bio' => [
                'nullable',
                'string'
            ],

            // Поля из partner_profiles (только для партнеров)
            'company_name' => [
                'required_if:role,partner',
                'nullable',
                'string',
                'max:255'
            ],
            'inn' => [
                'nullable',
                'string',
                'max:12'
            ],
            'address' => [
                'nullable',
                'string'
            ],
            'website' => [
                'nullable',
                'url'
            ],
            'description' => [
                'nullable',
                'string'
            ],
            'contact_person' => [
                'required_if:role,partner',
                'nullable',
                'string',
                'max:255'
            ],
            'contact_phone' => [
                'nullable',
                'string',
                'max:20'
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:5120' // 5MB
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
            'kubgtu_id.max' => 'ID КУБГТУ не должен превышать 255 символов.',
            'course.required_if' => 'Поле "Курс" обязательно для студентов.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'faculty_id.required_if' => 'Факультет обязателен для студентов.',
            'faculty_id.exists' => 'Выбранный факультет не существует.',
            'group.required_if' => 'Группа обязательна для студентов.',
            'group.max' => 'Название группы не должно превышать 255 символов.',
            'specialization.max' => 'Название специализации не должно превышать 255 символов.',
            'company_name.required_if' => 'Название компании обязательно для партнеров.',
            'company_name.max' => 'Название компании не должно превышать 255 символов.',
            'inn.max' => 'ИНН не должен превышать 12 символов.',
            'website.url' => 'Поле "Сайт" должно содержать корректный URL-адрес.',
            'contact_person.required_if' => 'Контактное лицо обязательно для партнеров.',
            'contact_person.max' => 'Имя контактного лица не должно превышать 255 символов.',
            'contact_phone.max' => 'Номер телефона не должен превышать 20 символов.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
            'logo.image' => 'Файл логотипа должен быть изображением.',
            'logo.mimes' => 'Логотип должен быть в формате: jpeg, png, jpg, gif или svg.',
            'logo.max' => 'Максимальный размер логотипа не должен превышать 5 МБ.',
        ];
    }
}
