<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Skill;

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
        $skill = $this->route('skill');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skills')->ignore($skill->id), // Исключаем текущий навык из проверки уникальности
            ],
            'category' => ['required', 'string', 'in:hard,soft,language,other'],
            'max_level' => ['required', 'integer', 'min:1', 'max:100'],
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
            'name.required' => 'Название навыка обязательно для заполнения.',
            'name.unique' => 'Навык с таким названием уже существует.',
            'category.required' => 'Категория навыка обязательна для заполнения.',
            'category.in' => 'Выбрана некорректная категория. Доступные: hard, soft, language, other.',
            'max_level.required' => 'Максимальный уровень навыка обязателен для заполнения.',
            'max_level.integer' => 'Максимальный уровень должен быть числом.',
            'max_level.min' => 'Максимальный уровень должен быть не меньше 1.',
            'max_level.max' => 'Максимальный уровень должен быть не больше 100.',
        ];
    }
}
