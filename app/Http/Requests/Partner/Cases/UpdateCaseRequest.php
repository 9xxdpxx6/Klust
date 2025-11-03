<?php

namespace App\Http\Requests\Partner\Cases;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseRequest extends FormRequest
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
            'partner_id' => 'sometimes|exists:partners,id',
            'title' => 'sometimes|string|max:255|min:5',
            'description' => 'sometimes|string|min:20|max:5000',
            'simulator_id' => 'nullable|exists:simulators,id',
            'deadline' => 'sometimes|date|after:today',
            'reward' => 'sometimes|string|min:5|max:1000',
            'required_team_size' => 'sometimes|integer|min:1|max:20',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_id.exists' => 'Выбранный партнер не существует',
            'title.min' => 'Название должно содержать минимум 5 символов',
            'title.max' => 'Название не должно превышать 255 символов',
            'description.min' => 'Описание должно содержать минимум 20 символов',
            'description.max' => 'Описание не должно превышать 5000 символов',
            'simulator_id.exists' => 'Выбранный симулятор не существует',
            'deadline.after' => 'Срок выполнения должен быть в будущем',
            'reward.min' => 'Описание награды должно содержать минимум 5 символов',
            'reward.max' => 'Описание награды не должно превышать 1000 символов',
            'required_team_size.min' => 'Размер команды должен быть не менее 1',
            'required_team_size.max' => 'Размер команды должен быть не более 20',
            'is_active.boolean' => 'Статус активности должен быть true или false',
        ];
    }
}
