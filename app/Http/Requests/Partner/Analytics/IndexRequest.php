<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный пользователь с ролью "партнер".
     */
    public function authorize(): bool
    {
        // Предполагаем, что у модели User есть метод isPartner()
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
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'], // Уточняем формат для удобства
            'date_to' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:date_from', // 'date_to' не может быть раньше 'date_from'
            ],
            'case_id' => [
                'nullable',
                'integer',
                'exists:cases,id', // Проверяем, что такой кейс вообще существует в системе
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
            'date_from.date' => 'Поле "Дата от" должно быть корректной датой.',
            'date_from.date_format' => 'Поле "Дата от" должно быть в формате ГГГГ-ММ-ДД.',
            'date_to.date' => 'Поле "Дата до" должно быть корректной датой.',
            'date_to.date_format' => 'Поле "Дата до" должно быть в формате ГГГГ-ММ-ДД.',
            'date_to.after_or_equal' => 'Поле "Дата до" не может быть раньше поля "Дата от".',
            'case_id.integer' => 'ID кейса должен быть числом.',
            'case_id.exists' => 'Выбранный кейс не существует.',
        ];
    }
}
