<?php

declare(strict_types=1);

namespace App\Http\Requests\Badges;

use Illuminate\Foundation\Http\FormRequest;

class StoreBadgesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Авторизация должна проверяться в контроллере через middleware
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
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string|min:10|max:1000',
            'required_points' => 'required|integer|min:0|max:10000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название бейджа обязательно для заполнения',
            'name.max' => 'Название не должно превышать 255 символов',
            'name.unique' => 'Бейдж с таким названием уже существует',
            'icon.image' => 'Иконка должна быть изображением',
            'icon.mimes' => 'Иконка должна быть в формате: jpeg, png, jpg, gif, svg',
            'icon.max' => 'Размер иконки не должен превышать 2MB',
            'description.required' => 'Описание обязательно для заполнения',
            'description.min' => 'Описание должно содержать минимум 10 символов',
            'description.max' => 'Описание не должно превышать 1000 символов',
            'required_points.required' => 'Количество необходимых очков обязательно',
            'required_points.integer' => 'Количество очков должно быть целым числом',
            'required_points.min' => 'Количество очков должно быть не менее 0',
            'required_points.max' => 'Количество очков должно быть не более 10000',
        ];
    }
}
