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
        return true; // Авторизация проверяется через middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'partner_id' => 'required|exists:partners,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50|max:10000',
            'simulator_id' => 'nullable|exists:simulators,id',
            'deadline' => 'required|date|after:today',
            'reward' => 'required|string|max:1000',
            'required_team_size' => 'required|integer|min:1|max:10',
            'status' => 'nullable|string|in:draft,active,completed,archived',
            'required_skills' => 'nullable|array',
            'required_skills.*' => 'exists:skills,id|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_id.required' => 'Партнер обязателен для выбора',
            'partner_id.exists' => 'Выбранный партнер не существует',
            'title.required' => 'Название кейса обязательно',
            'title.max' => 'Название не должно превышать 255 символов',
            'description.required' => 'Описание кейса обязательно',
            'description.min' => 'Описание должно содержать минимум 50 символов',
            'description.max' => 'Описание не должно превышать 10000 символов',
            'simulator_id.exists' => 'Выбранный симулятор не существует',
            'deadline.required' => 'Срок выполнения обязателен',
            'deadline.after' => 'Срок выполнения должен быть в будущем',
            'reward.required' => 'Награда обязательна для заполнения',
            'reward.max' => 'Описание награды не должно превышать 1000 символов',
            'required_team_size.required' => 'Размер команды обязателен',
            'required_team_size.min' => 'Размер команды должен быть не менее 1',
            'required_team_size.max' => 'Размер команды должен быть не более 10',
            'required_skills.*.exists' => 'Выбранный навык не существует',
            'required_skills.*.distinct' => 'Навыки должны быть уникальными',
        ];
    }
}

