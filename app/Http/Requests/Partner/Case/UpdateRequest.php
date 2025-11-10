<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Case;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный партнер и он является владельцем кейса.
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50', 'max:10000'],
            'simulator_id' => ['nullable', 'exists:simulators,id'],
            // Партнер может изменить дедлайн на любую дату
            'deadline' => ['required', 'date'],
            'reward' => ['required', 'string', 'max:1000'],
            'required_team_size' => ['required', 'integer', 'min:1', 'max:10'],
            // Партнер не может установить 'completed' или 'archived'
            'status' => ['nullable', 'string', 'in:draft,active'],

            // Валидация для навыков из pivot-таблицы
            'required_skills' => ['nullable', 'array'],
            'required_skills.*' => ['exists:skills,id', 'distinct'],
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
            'title.required' => 'Название кейса обязательно для заполнения.',
            'title.max' => 'Название кейса не должно превышать 255 символов.',
            'description.required' => 'Описание кейса обязательно для заполнения.',
            'description.min' => 'Описание кейса должно содержать минимум 50 символов.',
            'description.max' => 'Описание кейса не должно превышать 10000 символов.',
            'simulator_id.exists' => 'Выбранный симулятор не существует.',
            'deadline.required' => 'Дедлайн обязателен для заполнения.',
            'reward.required' => 'Описание награды обязательно для заполнения.',
            'reward.max' => 'Описание награды не должно превышать 1000 символов.',
            'required_team_size.required' => 'Размер команды обязателен для заполнения.',
            'required_team_size.min' => 'Размер команды должен быть не менее 1 человека.',
            'required_team_size.max' => 'Размер команды должен быть не более 10 человек.',
            'status.in' => 'Вы можете установить только статус "Черновик" или "Активный".',
            'required_skills.array' => 'Навыки должны быть переданы в виде массива.',
            'required_skills.*.exists' => 'Один из выбранных навыков не существует.',
            'required_skills.*.distinct' => 'Навыки не должны повторяться.',
        ];
    }
}
