<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Case;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'deadline' => ['required', 'date', 'after:today'],
            'reward' => ['required', 'string', 'max:1000'],
            'required_team_size' => ['required', 'integer', 'min:1', 'max:10'],
            'status' => ['required', 'string', 'in:draft,active,completed,archived'],
            'simulator_id' => ['nullable', 'integer', 'exists:simulators,id'],
            'required_skills' => ['nullable', 'array'],
            'required_skills.*' => ['integer', 'exists:skills,id'],
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
            'description.required' => 'Описание кейса обязательно для заполнения.',
            'user_id.required' => 'Необходимо выбрать партнера.',
            'user_id.exists' => 'Выбранный партнер не существует.',
            'deadline.required' => 'Необходимо указать дедлайн.',
            'deadline.after' => 'Дедлайн должен быть в будущем.',
            'reward.required' => 'Необходимо указать награду.',
            'required_team_size.required' => 'Необходимо указать размер команды.',
            'required_team_size.min' => 'Размер команды должен быть не меньше 1.',
            'required_team_size.max' => 'Размер команды должен быть не больше 10.',
            'status.required' => 'Необходимо указать статус.',
            'status.in' => 'Недопустимое значение статуса.',
            'simulator_id.exists' => 'Выбранный симулятор не существует.',
            'required_skills.*.exists' => 'Один из выбранных навыков не существует.',
        ];
    }
}
