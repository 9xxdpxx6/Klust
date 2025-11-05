<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'course' => 'nullable|integer|min:1|max:6',
            'role' => 'required|in:admin,teacher,student,partner',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation()
    {
        // Обрабатываем курс - преобразуем пустую строку в null
        if ($this->has('course') && ($this->course === '' || $this->course === 'null')) {
            $this->merge([
                'course' => null
            ]);
        }

        // Преобразуем курс в число, если он не null
        if ($this->has('course') && $this->course !== null && $this->course !== '') {
            $this->merge([
                'course' => (int) $this->course
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages()
    {
        return [
            'name.required' => 'Имя обязательно для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'role.required' => 'Роль обязательна для выбора',
            'role.in' => 'Выбранная роль не существует',
        ];
    }
}
