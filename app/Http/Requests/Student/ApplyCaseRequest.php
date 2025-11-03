<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('student');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */


    public function rules(): array
    {
        return [
            'case_id' => 'required|exists:cases,id',
            'proposal' => 'required|string|min:50|max:2000',
            'estimated_time_days' => 'required|integer|min:1|max:365',
            'bid_amount' => 'nullable|numeric|min:0|max:1000000',
            'team_members' => 'sometimes|array|max:5',
            'team_members.*' => 'exists:users,id',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,png,zip',
            'skills_demonstration' => 'sometimes|array',
            'skills_demonstration.*.skill_id' => 'required_with:skills_demonstration|exists:skills,id',
            'skills_demonstration.*.level' => 'required_with:skills_demonstration|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'case_id.required' => 'Выберите кейс для подачи заявки',
            'case_id.exists' => 'Выбранный кейс не существует',
            'proposal.required' => 'Предложение по решению кейса обязательно',
            'proposal.min' => 'Предложение должно содержать минимум 50 символов',
            'proposal.max' => 'Предложение не должно превышать 2000 символов',
            'estimated_time_days.required' => 'Укажите предполагаемое время выполнения',
            'estimated_time_days.min' => 'Время выполнения должно быть не менее 1 дня',
            'estimated_time_days.max' => 'Время выполнения должно быть не более 365 дней',
            'bid_amount.min' => 'Сумма заявки не может быть отрицательной',
            'bid_amount.max' => 'Сумма заявки слишком велика',
            'team_members.max' => 'Максимальное количество участников команды - 5',
            'team_members.*.exists' => 'Выбранный участник команды не существует',
            'attachments.max' => 'Максимум 5 файлов вложений',
            'attachments.*.max' => 'Размер каждого файла не должен превышать 10MB',
            'attachments.*.mimes' => 'Допустимые форматы файлов: pdf, doc, docx, jpg, png, zip',
            'skills_demonstration.*.skill_id.required' => 'ID навыка обязателен',
            'skills_demonstration.*.skill_id.exists' => 'Выбранный навык не существует',
            'skills_demonstration.*.level.required' => 'Уровень навыка обязателен',
            'skills_demonstration.*.level.min' => 'Уровень навыка должен быть не менее 1',
            'skills_demonstration.*.level.max' => 'Уровень навыка должен быть не более 100',
        ];
    }
}
