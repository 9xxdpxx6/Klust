<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        $userId = auth()->id();

        return [
            'name' => 'sometimes|string|max:255|min:2',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048|dimensions:max_width=500,max_height=500',
            'course' => 'nullable|integer|min:1|max:6',
            'phone' => 'nullable|string|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
            'bio' => 'nullable|string|max:1000',
            'social_links' => 'sometimes|array',
            'social_links.*.platform' => 'required_with:social_links|string|in:github,linkedin,telegram,vk,instagram',
            'social_links.*.url' => 'required_with:social_links|url',
            'skills' => 'sometimes|array',
            'skills.*.id' => 'required_with:skills|exists:skills,id',
            'skills.*.level' => 'required_with:skills|integer|min:0|max:100',
            'interests' => 'sometimes|array',
            'interests.*' => 'string|max:50',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Имя должно содержать минимум 2 символа',
            'name.max' => 'Имя не должно превышать 255 символов',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Пользователь с таким email уже существует',
            'avatar.image' => 'Файл должен быть изображением',
            'avatar.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif, webp',
            'avatar.max' => 'Размер изображения не должен превышать 2MB',
            'avatar.dimensions' => 'Размер изображения не должен превышать 500x500 пикселей',
            'course.min' => 'Курс должен быть от 1 до 6',
            'course.max' => 'Курс должен быть от 1 до 6',
            'phone.max' => 'Телефон не должен превышать 20 символов',
            'phone.regex' => 'Введите корректный номер телефона',
            'bio.max' => 'Биография не должна превышать 1000 символов',
            'social_links.*.platform.in' => 'Допустимые платформы: github, linkedin, telegram, vk, instagram',
            'social_links.*.url.url' => 'Введите корректный URL',
            'skills.*.id.exists' => 'Выбранный навык не существует',
            'skills.*.level.min' => 'Уровень навыка должен быть не менее 0',
            'skills.*.level.max' => 'Уровень навыка должен быть не более 100',
            'interests.*.max' => 'Каждый интерес не должен превышать 50 символов',
            'resume.mimes' => 'Резюме должно быть в формате: pdf, doc, docx',
            'resume.max' => 'Размер резюме не должен превышать 5MB',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'email' => 'Email',
            'avatar' => 'Аватар',
            'course' => 'Курс',
            'phone' => 'Телефон',
            'bio' => 'Биография',
            'resume' => 'Резюме',
        ];
    }
}
