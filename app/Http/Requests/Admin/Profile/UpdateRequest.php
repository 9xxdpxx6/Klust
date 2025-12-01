<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB
            ],
            'phone' => ['nullable', 'string', 'max:20'],
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
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.regex' => 'Email должен быть корректным адресом электронной почты.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'avatar.image' => 'Файл аватара должен быть изображением.',
            'avatar.mimes' => 'Аватар должен быть в формате: jpeg, png, jpg, gif.',
            'avatar.max' => 'Максимальный размер аватара не должен превышать 2 МБ.',
            'phone.max' => 'Номер телефона не должен превышать 20 символов.',
            'bio.max' => 'Биография не должна превышать 1000 символов.',
        ];
    }
}
