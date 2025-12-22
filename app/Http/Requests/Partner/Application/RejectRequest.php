<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

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

    /**
     * Handle a failed validation attempt.
     * Переопределяем чтобы редиректить на страницу кейса вместо back(),
     * так как back() может попытаться сделать GET запрос к POST маршруту.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Получаем case из маршрута
        $case = $this->route('case');
        
        // Для Inertia запросов ValidationException автоматически возвращает JSON с ошибками
        // и не делает редирект. Для обычных запросов редиректим на страницу кейса.
        // Важно: не используем back() чтобы избежать GET запроса к POST маршруту
        $exception = (new ValidationException($validator));
        
        // Если это не Inertia запрос, устанавливаем редирект
        if (!$this->header('X-Inertia')) {
            $exception->redirectTo(route('partner.cases.show', $case));
        }
        
        throw $exception;
    }
}
