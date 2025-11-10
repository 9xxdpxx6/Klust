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
        $user = $this->route('user'); // Получаем пользователя для правила unique

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id) // Исключаем текущего пользователя из проверки уникальности
            ],
            'course' => ['nullable', 'integer', 'between:1,6'], // Предположим, курсы от 1 до 6

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],

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
            'name.required' => 'Имя обязательно для заполнения.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'course.between' => 'Курс должен быть в диапазоне от 1 до 6.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
            'faculty_id.exists' => 'Выбранный факультет не существует.',
            'group.max' => 'Название группы не должно превышать 255 символов.',
            'specialization.max' => 'Название специализации не должно превышать 255 символов.',
        ];
    }
}
