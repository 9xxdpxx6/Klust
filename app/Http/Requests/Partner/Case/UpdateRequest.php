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
            'description' => ['required', 'string'],
            'simulator_id' => ['nullable', 'integer', 'exists:simulators,id'],
            // Партнер может изменить дедлайн на любую дату
            'deadline' => ['required', 'date'],
            'reward' => ['required', 'string'],
            'required_team_size' => ['required', 'integer', 'between:1,10'],
            // Партнер не может установить 'completed' или 'archived'
            'status' => ['required', 'string', 'in:draft,active'],

            // Валидация для навыков из pivot-таблицы
            'skills' => ['array'],
            'skills.*' => ['required', 'integer', 'exists:skills,id'],
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
            'simulator_id.exists' => 'Выбранный симулятор не существует.',
            'deadline.required' => 'Дедлайн обязателен для заполнения.',
            'reward.required' => 'Описание награды обязательно для заполнения.',
            'required_team_size.required' => 'Размер команды обязателен для заполнения.',
            'required_team_size.between' => 'Размер команды должен быть в диапазоне от 1 до 10 человек.',
            'status.required' => 'Статус обязателен для заполнения.',
            'status.in' => 'Вы можете установить только статус "Черновик" или "Активный".',
            'skills.array' => 'Навыки должны быть переданы в виде массива.',
            'skills.*.required' => 'Идентификатор навыка обязателен.',
            'skills.*.exists' => 'Один из выбранных навыков не существует.',
        ];
    }
}
