<?php

namespace App\Http\Requests\Skills;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSkillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $skillId = $this->route('skill');

        return [
            'name' => 'sometimes|string|max:100|min:2|unique:skills,name,' . $skillId,
            'category' => 'sometimes|string|in:hard,soft,technical,business,creative,language',
            'max_level' => 'sometimes|integer|min:1|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Название навыка должно содержать минимум 2 символа',
            'name.max' => 'Название навыка не должно превышать 100 символов',
            'name.unique' => 'Навык с таким названием уже существует',
            'category.in' => 'Выбрана недопустимая категория. Допустимые: hard, soft, technical, business, creative, language',
            'max_level.integer' => 'Максимальный уровень должен быть целым числом',
            'max_level.min' => 'Максимальный уровень должен быть не менее 1',
            'max_level.max' => 'Максимальный уровень должен быть не более 1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Название навыка',
            'category' => 'Категория',
            'max_level' => 'Максимальный уровень',
        ];
    }
}
