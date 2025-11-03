<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class CompleteSimulatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */


    public function rules(): array
    {
        return [
            'simulator_id' => 'required|exists:simulators,id',
            'score' => 'required|integer|min:0|max:100',
            'time_spent_minutes' => 'required|integer|min:1|max:1440',
            'completed_at' => 'sometimes|date|before_or_equal:now',
            'answers' => 'sometimes|array',
            'answers.*.question_id' => 'required_with:answers|exists:simulator_questions,id',
            'answers.*.answer' => 'required_with:answers|string',
            'answers.*.is_correct' => 'sometimes|boolean',
            'feedback' => 'nullable|string|max:1000',
            'achievements' => 'sometimes|array',
            'achievements.*' => 'exists:achievements,id',
            'skills_gained' => 'sometimes|array',
            'skills_gained.*.skill_id' => 'required_with:skills_gained|exists:skills,id',
            'skills_gained.*.points' => 'required_with:skills_gained|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'simulator_id.required' => 'ID симулятора обязателен',
            'simulator_id.exists' => 'Выбранный симулятор не существует',
            'score.required' => 'Результат выполнения обязателен',
            'score.min' => 'Результат не может быть отрицательным',
            'score.max' => 'Результат не может превышать 100%',
            'time_spent_minutes.required' => 'Время выполнения обязательно',
            'time_spent_minutes.min' => 'Время выполнения должно быть не менее 1 минуты',
            'time_spent_minutes.max' => 'Время выполнения должно быть не более 1440 минут (24 часа)',
            'completed_at.before_or_equal' => 'Дата завершения не может быть в будущем',
            'answers.*.question_id.required' => 'ID вопроса обязателен',
            'answers.*.question_id.exists' => 'Выбранный вопрос не существует',
            'answers.*.answer.required' => 'Ответ на вопрос обязателен',
            'answers.*.is_correct.boolean' => 'Статус правильности ответа должен быть true или false',
            'feedback.max' => 'Отзыв не должен превышать 1000 символов',
            'achievements.*.exists' => 'Выбранное достижение не существует',
            'skills_gained.*.skill_id.required' => 'ID навыка обязателен',
            'skills_gained.*.skill_id.exists' => 'Выбранный навык не существует',
            'skills_gained.*.points.required' => 'Количество очков обязательно',
            'skills_gained.*.points.min' => 'Количество очков должно быть не менее 1',
            'skills_gained.*.points.max' => 'Количество очков должно быть не более 100',
        ];
    }
}
