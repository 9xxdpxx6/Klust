<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Case;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный пользователь с ролью "партнер".
     */
    public function authorize(): bool
    {
        // Пример проверки на роль партнера
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
            // 'partner_id' отсутствует, так как он берется из auth()->user()->id
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            // Партнер может выбрать любой симулятор, проверяем только на существование
            'simulator_id' => ['nullable', 'integer', 'exists:simulators,id'],
            'deadline' => ['required', 'date', 'after_or_equal:today'], // Дедлайн должен быть в будущем
            'reward' => ['required', 'string'],
            'required_team_size' => ['required', 'integer', 'between:1,10'],
            // Партнер может установить только 'draft' или 'active'
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
            'deadline.after_or_equal' => 'Дедлайн должен быть сегодня или в будущем.',
            'reward.required' => 'Описание награды обязательно для заполнения.',
            'required_team_size.required' => 'Размер команды обязателен для заполнения.',
            'required_team_size.between' => 'Размер команды должен быть в диапазоне от 1 до 10 человек.',
            'status.required' => 'Статус обязателен для заполнения.',
            'status.in' => 'Можно выбрать только статус "Черновик" или "Активный".',
            'skills.array' => 'Навыки должны быть переданы в виде массива.',
            'skills.*.required' => 'Идентификатор навыка обязателен.',
            'skills.*.exists' => 'Один из выбранных навыков не существует.',
        ];
    }
}
