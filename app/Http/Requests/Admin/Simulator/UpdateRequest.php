<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Simulator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Импортируем для Rule::unique

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Замените на вашу логику
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Предполагаем, что в роуте есть {simulator}
        $simulator = $this->route('simulator');

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('simulators')->ignore($simulator->id), // Исключаем текущий симулятор
                'regex:/^[a-z0-9_-]+$/',
            ],
            'description' => ['required', 'string'],
            'is_active' => ['boolean'],

            // Превью-изображение по общим правилам
            'preview_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
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
            'user_id.required' => 'Необходимо выбрать партнера.',
            'user_id.exists' => 'Выбранный партнер не существует.',
            'title.required' => 'Название симулятора обязательно для заполнения.',
            'slug.required' => 'URL-идентификатор (slug) обязателен для заполнения.',
            'slug.unique' => 'Такой URL-идентификатор уже используется.',
            'slug.regex' => 'URL-идентификатор может содержать только строчные буквы, цифры, дефис и подчеркивание.',
            'description.required' => 'Описание симулятора обязательно для заполнения.',
            'preview_image.image' => 'Файл превью должен быть изображением.',
            'preview_image.mimes' => 'Превью должно быть в формате: jpeg, png, jpg, gif.',
            'preview_image.max' => 'Максимальный размер превью не должен превышать 5 МБ.',
        ];
    }
}
