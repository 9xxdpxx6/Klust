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
            'role' => ['sometimes', 'required', 'string', 'in:student,teacher,admin'],

            // Используем sometimes для полей, которые могут быть в запросе
            'kubgtu_id' => [
                'sometimes',
                'required_if:role,student',
                'string',
                Rule::unique('users', 'kubgtu_id')->ignore($userId)
            ],
            'course' => [
                'sometimes',
                'required_if:role,student',
                'integer',
                'between:1,6'
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
     * Configure the validator instance.
     * Дополнительная условная валидация через sometimes.
     */
    public function withValidator($validator)
    {
        // Если в запросе есть поле 'role' и оно 'student',
        // то поля 'kubgtu_id' и 'course' становятся обязательными.
        // Это альтернатива 'required_if' в rules(), полезно для сложной логики.
        $validator->sometimes(['kubgtu_id', 'course'], 'required', function ($input) {
            return isset($input->role) && $input->role === 'student';
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
            'course.required' => 'Поле "Курс" обязательно для студентов.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
        ];
    }
}
