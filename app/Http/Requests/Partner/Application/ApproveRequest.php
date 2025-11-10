<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
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
            'comment' => ['nullable', 'string', 'max:1000'],
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
            'comment.max' => 'Комментарий не должен превышать 1000 символов.',
        ];
    }
}
