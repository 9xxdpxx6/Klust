<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Badge;

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
        // Предполагаем, что в роуте есть {badge}
        $badge = $this->route('badge');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('badges')->ignore($badge->id) // Исключаем текущий бейдж из проверки уникальности
            ],
            'description' => ['required', 'string'],
            'required_points' => ['required', 'integer', 'min:1'],

            // Иконка по правилам для "иконок бейджей"
            'icon' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // 2MB
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
            'name.required' => 'Название бейджа обязательно для заполнения.',
            'name.unique' => 'Бейдж с таким названием уже существует.',
            'description.required' => 'Описание бейджа обязательно для заполнения.',
            'required_points.required' => 'Необходимо указать количество очков для получения.',
            'required_points.integer' => 'Количество очков должно быть числом.',
            'required_points.min' => 'Количество очков должно быть не меньше 1.',
            'icon.image' => 'Файл иконки должен быть изображением.',
            'icon.mimes' => 'Иконка должна быть в формате: jpeg, png, jpg, gif или svg.',
            'icon.max' => 'Максимальный размер иконки не должен превышать 2 МБ.',
        ];
    }
}
