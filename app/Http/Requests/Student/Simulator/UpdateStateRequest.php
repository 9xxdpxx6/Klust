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
            // State is a dynamic JSON blob that stores simulator session progress.
            // We validate the top-level structure and known fields but allow
            // additional dynamic keys (dialogue_type, max_score, variants_progress, etc.)
            // to pass through — the controller uses $request->input('state').
            'state' => ['required', 'array'],

            // Стадия
            'state.current_stage' => ['nullable', 'string'],

            // Данные клиента (опционально)
            'state.client' => ['nullable', 'array'],

            // Диалог (опционально)
            'state.dialogue' => ['nullable', 'array'],
            'state.dialogue.messages' => ['nullable', 'array'],
            'state.dialogue.current_step' => ['nullable', 'string'],
            'state.dialogue.selected_options' => ['nullable', 'array'],
            'state.dialogue.formData' => ['nullable', 'array'],

            // Расчеты (опционально)
            'state.calculations' => ['nullable', 'array'],

            // UI состояние
            'state.ui' => ['nullable', 'array'],

            // Система баллов
            'state.score' => ['nullable', 'numeric'],
            'state.score_history' => ['nullable', 'array'],

            // Действия
            'state.actions' => ['nullable', 'array'],

            // Динамические поля (выставляются backend-ом через processDialogueActions)
            'state.dialogue_type' => ['nullable', 'string'],
            'state.max_score' => ['nullable', 'numeric'],
            'state.variants_progress' => ['nullable', 'array'],

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
            'state.required' => 'Поле state обязательно для заполнения.',
            'state.array' => 'Поле state должно быть массивом.',
            'state.current_stage.string' => 'Текущий этап должен быть строкой.',
            'state.client.array' => 'Данные клиента должны быть массивом.',
            'state.dialogue.array' => 'Данные диалога должны быть массивом.',
            'state.dialogue.messages.array' => 'Сообщения должны быть массивом.',
            'state.dialogue.current_step.string' => 'Текущий шаг должен быть строкой.',
            'state.calculations.array' => 'Данные расчетов должны быть массивом.',
            'state.ui.array' => 'UI состояние должно быть массивом.',
            'state.score.numeric' => 'Баллы должны быть числом.',
            'state.score_history.array' => 'История баллов должна быть массивом.',
            'state.actions.array' => 'Действия должны быть массивом.',
            'state.errors.array' => 'Ошибки должны быть массивом.',
            'state.dialogue_type.string' => 'Тип диалога должен быть строкой.',
            'state.max_score.numeric' => 'Максимальный балл должен быть числом.',
            'state.variants_progress.array' => 'Прогресс вариантов должен быть массивом.',
        ];
    }
}
