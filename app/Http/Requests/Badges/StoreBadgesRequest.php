<?php

namespace App\Http\Requests\Badges;

use Illuminate\Foundation\Http\FormRequest;

class StoreBadgesRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|unique:badges,name',
            'icon' => 'nullable|string|max:255',
            'description' => 'required|string|min:10|max:1000',
            'required_points' => 'required|integer|min:1|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название бейджа обязательно для заполнения',
            'name.max' => 'Название не должно превышать 255 символов',
            'name.unique' => 'Бейдж с таким названием уже существует',
            'icon.max' => 'Путь к иконке не должен превышать 255 символов',
            'description.required' => 'Описание обязательно для заполнения',
            'description.min' => 'Описание должно содержать минимум 10 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'required_points.required' => 'Количество необходимых очков обязательно',
            'required_points.integer' => 'Количество очков должно быть целым числом',
            'required_points.min' => 'Количество очков должно быть не менее 1',
            'required_points.max' => 'Количество очков должно быть не более 10000',
        ];
    }
}
