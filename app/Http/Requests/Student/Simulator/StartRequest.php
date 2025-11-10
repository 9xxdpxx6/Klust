<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Simulator;

use Illuminate\Foundation\Http\FormRequest;

class StartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Проверяем, что это аутентифицированный пользователь с ролью "студент".
     */
    public function authorize(): bool
    {
        // Предполагаем, что у модели User есть метод isStudent()
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * В этом запросе нет полей в теле, поэтому правила не нужны.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }
}
