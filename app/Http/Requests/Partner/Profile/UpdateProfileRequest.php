<?php

namespace App\Http\Requests\Partner\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $partnerProfile = $this->route('partner_profile');
        return $partnerProfile && $partnerProfile->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $partnerProfileId = $this->route('partner_profile');

        return [
            'company_name' => 'sometimes|string|max:255|min:2',
            'inn' => 'nullable|string|size:10|regex:/^\d{10}$/',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|min:10|max:2000',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20|regex:/^\+?[0-9\s\-\(\)]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.min' => 'Название компании должно содержать минимум 2 символа',
            'company_name.max' => 'Название компании не должно превышать 255 символов',
            'inn.size' => 'ИНН должен содержать 10 цифр',
            'inn.regex' => 'ИНН должен содержать только цифры',
            'address.max' => 'Адрес не должен превышать 500 символов',
            'website.url' => 'Введите корректный URL сайта',
            'website.max' => 'URL сайта не должен превышать 255 символов',
            'description.min' => 'Описание должно содержать минимум 10 символов',
            'description.max' => 'Описание не должно превышать 2000 символов',
            'contact_person.max' => 'ФИО контактного лица не должно превышать 255 символов',
            'contact_phone.max' => 'Телефон не должен превышать 20 символов',
            'contact_phone.regex' => 'Введите корректный номер телефона',
        ];
    }
}
