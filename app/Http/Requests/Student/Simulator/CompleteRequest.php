<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Simulator;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный пользователь с ролью "студент".
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
            'score' => ['required', 'integer', 'between:0,100'], // Максимальный балл: 100

            // Новые поля
            'time_spent' => ['required', 'integer', 'min:1'], // Время, затраченное на прохождение, в секундах
            'answers' => ['nullable', 'array'], // Массив с ответами студента (для детального анализа)
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
            'score.required' => 'Балл обязателен для завершения сессии.',
            'score.integer' => 'Балл должен быть целым числом.',
            'score.between' => 'Балл должен быть в диапазоне от 0 до 100.',

            // Сообщения для новых полей
            'time_spent.required' => 'Необходимо указать время, затраченное на прохождение.',
            'time_spent.integer' => 'Время должно быть целым числом (в секундах).',
            'time_spent.min' => 'Время не может быть меньше 1 секунды.',
            'answers.array' => 'Ответы должны быть переданы в виде массива.',
        ];
    }
}
