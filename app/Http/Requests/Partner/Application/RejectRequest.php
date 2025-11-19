<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный пользователь с ролью "партнер".
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
            // Причина отклонения обязательна для предоставления обратной связи
            'rejection_reason' => [
                'required',
                'string',
                'min:10', // Минимальная длина, чтобы избежать бессмысленных ответов
                'max:1000',
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
            'rejection_reason.required' => 'Укажите причину отклонения заявки.',
            'rejection_reason.min' => 'Причина отклонения должна содержать минимум 10 символов.',
            'rejection_reason.max' => 'Причина отклонения не должна превышать 1000 символов.',
        ];
    }
}
