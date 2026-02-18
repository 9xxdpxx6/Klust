<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Simulator;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStateRequest extends FormRequest
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
            // Основная структура
            'state' => ['required', 'array'],
            'state.current_stage' => ['nullable', 'string'],

            // Данные клиента (опционально)
            'state.client' => ['nullable', 'array'],
            'state.client.id' => ['nullable', 'string'],
            'state.client.type' => ['nullable', 'string'],
            'state.client.name' => ['nullable', 'string'],
            'state.client.age' => ['nullable', 'integer', 'min:18', 'max:100'],
            // Allow null or 0 for reset (will be treated as not set)
            'state.client.income' => ['nullable', 'numeric', 'min:0'],
            'state.client.expenses' => ['nullable', 'numeric', 'min:0'],
            'state.client.credit_history' => ['nullable', 'string', 'in:excellent,good,fair,poor,none'],
            'state.client.has_deposit' => ['nullable', 'boolean'],
            'state.client.model_path' => ['nullable', 'string'],

            // Диалог (опционально)
            'state.dialogue' => ['nullable', 'array'],
            'state.dialogue.messages' => ['nullable', 'array'],
            'state.dialogue.messages.*.role' => ['required_with:state.dialogue.messages', 'string', 'in:client,user'],
            'state.dialogue.messages.*.text' => ['required_with:state.dialogue.messages', 'string'],
            'state.dialogue.messages.*.timestamp' => ['required_with:state.dialogue.messages', 'string', 'date'],
            'state.dialogue.current_step' => ['nullable', 'string'],
            'state.dialogue.selected_options' => ['nullable', 'array'],
            'state.dialogue.formData' => ['nullable', 'array'],

            // Расчеты (опционально)
            'state.calculations' => ['nullable', 'array'],
            'state.calculations.credit_score' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'state.calculations.credit_limit' => ['nullable', 'numeric', 'min:0'],
            'state.calculations.interest_rate' => ['nullable', 'numeric', 'min:0'],
            'state.calculations.monthly_payment' => ['nullable', 'numeric', 'min:0'],
            'state.calculations.deposit_result' => ['nullable', 'numeric', 'min:0'],
            'state.calculations.decision' => ['nullable', 'string'],

            // UI состояние
            'state.ui' => ['nullable', 'array'],
            'state.ui.activeDialog' => ['nullable', 'string', 'in:phone,calculator,documents,dialogue'],
            
            // Система баллов
            'state.score' => ['nullable', 'numeric', 'min:0'],
            'state.score_history' => ['nullable', 'array'],
            'state.score_history.*.points' => ['required_with:state.score_history', 'integer'],
            'state.score_history.*.reason' => ['nullable', 'string'],
            'state.score_history.*.timestamp' => ['required_with:state.score_history', 'string', 'date'],
            
            // Действия
            'state.actions' => ['nullable', 'array'],
            'state.actions.*.type' => ['required_with:state.actions', 'string'],
            'state.actions.*.field' => ['nullable', 'string'],
            'state.actions.*.value' => ['nullable'],
            'state.actions.*.calculation' => ['nullable', 'string'],
            'state.actions.*.timestamp' => ['required_with:state.actions', 'string', 'date'],

            // Ошибки
            'state.errors' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Основная структура
            'state.required' => 'Поле state обязательно для заполнения.',
            'state.array' => 'Поле state должно быть массивом.',
            'state.current_stage.string' => 'Текущий этап должен быть строкой.',

            // Данные клиента
            'state.client.array' => 'Данные клиента должны быть массивом.',
            'state.client.id.string' => 'ID клиента должен быть строкой.',
            'state.client.type.string' => 'Тип клиента должен быть строкой.',
            'state.client.name.string' => 'Имя клиента должно быть строкой.',
            'state.client.age.integer' => 'Возраст клиента должен быть целым числом.',
            'state.client.age.min' => 'Возраст клиента должен быть не менее 18 лет.',
            'state.client.age.max' => 'Возраст клиента должен быть не более 100 лет.',
            'state.client.income.numeric' => 'Доход клиента должен быть числом.',
            'state.client.income.min' => 'Доход клиента не может быть отрицательным.',
            'state.client.expenses.numeric' => 'Расходы клиента должны быть числом.',
            'state.client.expenses.min' => 'Расходы клиента не могут быть отрицательными.',
            'state.client.credit_history.string' => 'Кредитная история должна быть строкой.',
            'state.client.credit_history.in' => 'Кредитная история должна быть одним из: excellent, good, fair, poor, none.',
            'state.client.has_deposit.boolean' => 'Поле наличия вклада должно быть логическим значением.',
            'state.client.model_path.string' => 'Путь к модели должен быть строкой.',

            // Диалог
            'state.dialogue.array' => 'Данные диалога должны быть массивом.',
            'state.dialogue.messages.array' => 'Сообщения должны быть массивом.',
            'state.dialogue.messages.*.role.required_with' => 'Роль отправителя обязательна для сообщения.',
            'state.dialogue.messages.*.role.string' => 'Роль отправителя должна быть строкой.',
            'state.dialogue.messages.*.role.in' => 'Роль отправителя должна быть client или user.',
            'state.dialogue.messages.*.text.required_with' => 'Текст сообщения обязателен для заполнения.',
            'state.dialogue.messages.*.text.string' => 'Текст сообщения должен быть строкой.',
            'state.dialogue.messages.*.timestamp.required_with' => 'Временная метка сообщения обязательна для заполнения.',
            'state.dialogue.messages.*.timestamp.string' => 'Временная метка сообщения должна быть строкой.',
            'state.dialogue.messages.*.timestamp.date' => 'Временная метка сообщения должна быть валидной датой.',
            'state.dialogue.current_step.string' => 'Текущий шаг должен быть строкой.',
            'state.dialogue.selected_options.array' => 'Выбранные опции должны быть массивом.',
            'state.dialogue.formData.array' => 'Данные формы должны быть массивом.',

            // Расчеты
            'state.calculations.array' => 'Данные расчетов должны быть массивом.',
            'state.calculations.credit_score.numeric' => 'Балл скоринга должен быть числом.',
            'state.calculations.credit_score.min' => 'Балл скоринга не может быть отрицательным.',
            'state.calculations.credit_score.max' => 'Балл скоринга не может превышать 1.',
            'state.calculations.credit_limit.numeric' => 'Кредитный лимит должен быть числом.',
            'state.calculations.credit_limit.min' => 'Кредитный лимит не может быть отрицательным.',
            'state.calculations.interest_rate.numeric' => 'Процентная ставка должна быть числом.',
            'state.calculations.interest_rate.min' => 'Процентная ставка не может быть отрицательной.',
            'state.calculations.monthly_payment.numeric' => 'Ежемесячный платеж должен быть числом.',
            'state.calculations.monthly_payment.min' => 'Ежемесячный платеж не может быть отрицательным.',
            'state.calculations.deposit_result.numeric' => 'Результат расчета вклада должен быть числом.',
            'state.calculations.deposit_result.min' => 'Результат расчета вклада не может быть отрицательным.',
            'state.calculations.decision.string' => 'Решение должно быть строкой.',

            // UI состояние
            'state.ui.array' => 'UI состояние должно быть массивом.',
            'state.ui.activeDialog.string' => 'Активный диалог должен быть строкой.',
            'state.ui.activeDialog.in' => 'Активный диалог должен быть одним из: phone, calculator, documents, dialogue.',

            // Действия
            'state.actions.array' => 'Действия должны быть массивом.',
            'state.actions.*.type.required_with' => 'Тип действия обязателен для заполнения.',
            'state.actions.*.type.string' => 'Тип действия должен быть строкой.',
            'state.actions.*.field.string' => 'Поле действия должно быть строкой.',
            'state.actions.*.calculation.string' => 'Тип расчета должен быть строкой.',
            'state.actions.*.timestamp.required_with' => 'Временная метка действия обязательна для заполнения.',
            'state.actions.*.timestamp.string' => 'Временная метка действия должна быть строкой.',
            'state.actions.*.timestamp.date' => 'Временная метка действия должна быть валидной датой.',

            // Ошибки
            'state.errors.array' => 'Ошибки должны быть массивом.',
        ];
    }
}
