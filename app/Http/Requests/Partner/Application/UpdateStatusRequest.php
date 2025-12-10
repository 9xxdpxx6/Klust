<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,accepted,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Статус обязателен для указания.',
            'status.in' => 'Недопустимый статус.',
        ];
    }
}

