<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Badge;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Предполагаем, что только администратор может создавать бейджи.
     */
    public function authorize(): bool
    {
        return true; // Замените на вашу логику, например: auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:badges,name'],
            'description' => ['required', 'string'],
            'required_points' => ['required', 'integer', 'min:1'],

            // Иконка - строка с именем PrimeIcon (например, 'pi-star', 'pi-trophy')
            'icon' => [
                'nullable',
                'string',
                'max:255',
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
            'icon.string' => 'Иконка должна быть строкой.',
            'icon.max' => 'Название иконки не должно превышать 255 символов.',
        ];
    }
}
