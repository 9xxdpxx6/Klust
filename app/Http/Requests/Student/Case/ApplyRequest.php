<?php

declare(strict_types=1);

namespace App\Http\Requests\Student\Case;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Мотивационное письмо (обязательно)
            'motivation' => [
                'required',
                'string',
                'max:5000',
            ],
            // Поле с участниками команды. Может быть пустым, если студент подает заявку один.
            'team_members' => [
                'array',
                'max:9', // Лидер может пригласить до 9 участников
                'distinct', // Все ID в массиве должны быть уникальными
            ],
            // Каждый ID участника в массиве должен быть целым числом и существовать в таблице users
            'team_members.*' => [
                'required', // Обязателен для каждого элемента массива
                'integer',
                'exists:users,id',
            ],
            // Email'ы участников команды (альтернатива team_members)
            'team_member_emails' => [
                'nullable',
                'array',
                'max:9',
            ],
            'team_member_emails.*' => [
                'required',
                'email',
                'exists:users,email',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     * Добавляем кастомную проверку: лидер не может добавить сам себя в список участников.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $teamMembers = $this->input('team_members', []);
            $teamMemberEmails = $this->input('team_member_emails', []);
            $currentUserId = auth()->id();
            $currentUserEmail = auth()->user()->email;

            // Проверка для team_members
            if (in_array($currentUserId, $teamMembers)) {
                $validator->errors()->add('team_members', 'Вы не можете добавить самого себя в список участников, так как вы автоматически являетесь лидером команды.');
            }

            // Проверка для team_member_emails
            if (in_array($currentUserEmail, $teamMemberEmails)) {
                $validator->errors()->add('team_member_emails', 'Вы не можете добавить самого себя в список участников, так как вы автоматически являетесь лидером команды.');
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
            'motivation.required' => 'Мотивационное письмо обязательно для заполнения.',
            'motivation.string' => 'Мотивационное письмо должно быть текстом.',
            'motivation.max' => 'Мотивационное письмо не должно превышать 5000 символов.',
            'team_members.array' => 'Список участников должен быть массивом ID.',
            'team_members.max' => 'Вы можете добавить не более 9 участников.',
            'team_members.distinct' => 'Список участников не может содержать повторяющихся ID.',
            'team_members.*.required' => 'ID участника обязателен.',
            'team_members.*.integer' => 'ID каждого участника должен быть числом.',
            'team_members.*.exists' => 'Один из указанных участников не найден в системе.',
            'team_member_emails.array' => 'Список email участников должен быть массивом.',
            'team_member_emails.max' => 'Вы можете добавить не более 9 участников.',
            'team_member_emails.*.required' => 'Email участника обязателен.',
            'team_member_emails.*.email' => 'Один из указанных email имеет некорректный формат.',
            'team_member_emails.*.exists' => 'Пользователь с email :input не найден в системе. Убедитесь, что участник зарегистрирован.',
        ];
    }
}
