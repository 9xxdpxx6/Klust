<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Импортируем для сложных правил

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Пользователь может редактировать свой профиль или администратор - любой.
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
        $userId = $this->route('user')->id; // Получаем ID пользователя из маршрута

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId) // Исключаем текущего пользователя из проверки уникальности
            ],
            // Пароль опционален. Если есть - должен быть валидным.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', 'required', 'string', 'in:student,teacher,partner,admin'],

            // Поля из основной таблицы users
            'kubgtu_id' => [
                'sometimes',
                'required_if:role,student',
                'string',
                'max:255',
                Rule::unique('users', 'kubgtu_id')->ignore($userId)
            ],
            'course' => [
                'sometimes',
                'required_if:role,student',
                'integer',
                'between:1,6'
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],

            // Поля из student_profiles (только для студентов)
            'faculty_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:faculties,id'
            ],
            'group' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'specialization' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'bio' => [
                'sometimes',
                'nullable',
                'string'
            ],

            // Поля из partner_profiles (только для партнеров)
            'company_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'inn' => [
                'sometimes',
                'nullable',
                'string',
                'max:12'
            ],
            'address' => [
                'sometimes',
                'nullable',
                'string'
            ],
            'website' => [
                'sometimes',
                'nullable',
                'url'
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string'
            ],
            'contact_person' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'contact_phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20'
            ],
            'logo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:5120' // 5MB
            ],
        ];
    }

    /**
     * Configure the validator instance.
     * Дополнительная условная валидация через sometimes.
     */
    public function withValidator($validator)
    {
        // Если в запросе есть поле 'role' и оно 'student',
        // то поля 'kubgtu_id', 'course', 'faculty_id', 'group' становятся обязательными.
        $validator->sometimes(['kubgtu_id', 'course', 'faculty_id', 'group'], 'required', function ($input) {
            return isset($input->role) && $input->role === 'student';
        });

        // Если в запросе есть поле 'role' и оно 'partner',
        // то поля 'company_name', 'contact_person' становятся обязательными.
        $validator->sometimes(['company_name', 'contact_person'], 'required', function ($input) {
            return isset($input->role) && $input->role === 'partner';
        });
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
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'role.in' => 'Выбрана некорректная роль.',
            'kubgtu_id.required' => 'Поле "ID КУБГТУ" обязательно для студентов.',
            'kubgtu_id.unique' => 'Пользователь с таким ID КУБГТУ уже существует.',
            'kubgtu_id.max' => 'ID КУБГТУ не должен превышать 255 символов.',
            'course.required' => 'Поле "Курс" обязательно для студентов.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'faculty_id.exists' => 'Выбранный факультет не существует.',
            'group.max' => 'Название группы не должно превышать 255 символов.',
            'specialization.max' => 'Название специализации не должно превышать 255 символов.',
            'company_name.required' => 'Название компании обязательно для партнеров.',
            'company_name.max' => 'Название компании не должно превышать 255 символов.',
            'inn.max' => 'ИНН не должен превышать 12 символов.',
            'website.url' => 'Поле "Сайт" должно содержать корректный URL-адрес.',
            'contact_person.required' => 'Контактное лицо обязательно для партнеров.',
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
