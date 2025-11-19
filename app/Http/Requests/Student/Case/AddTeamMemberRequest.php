<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Case;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMemberRequest extends FormRequest
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
            // Ожидаем ID нового участника команды
            'user_id' => [
                'required',
                'integer',
                'exists:users,id', // Проверяем, что такой пользователь вообще существует
            ],
        ];
    }

    /**
     * Configure the validator instance.
     * Добавляем проверку, чтобы лидер не мог добавить сам себя.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newMemberId = $this->input('user_id');
            $currentLeaderId = auth()->id();

            if ($newMemberId == $currentLeaderId) {
                $validator->errors()->add('user_id', 'Вы не можете добавить самого себя в команду, так как уже являетесь ее лидером.');
            }
        });
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'ID участника обязателен для добавления.',
            'user_id.integer' => 'ID участника должен быть числом.',
            'user_id.exists' => 'Указанный пользователь не найден в системе.',
        ];
    }
}
