<?php

namespace App\Http\Requests\Simulators;

use Illuminate\Foundation\Http\FormRequest;

class StoreSimulatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'partner_id' => 'required|exists:partners,id',
            'title' => 'required|string|max:255|min:3',
            'slug' => 'required|string|max:255|unique:simulators,slug|regex:/^[a-z0-9-]+$/',
            'description' => 'required|string|min:20|max:2000',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'partner_id.required' => 'Партнер обязателен для выбора',
            'partner_id.exists' => 'Выбранный партнер не существует',
            'title.required' => 'Название симулятора обязательно',
            'title.min' => 'Название должно содержать минимум 3 символа',
            'title.max' => 'Название не должно превышать 255 символов',
            'slug.required' => 'SLUG обязателен для заполнения',
            'slug.unique' => 'Симулятор с таким SLUG уже существует',
            'slug.regex' => 'SLUG может содержать только латинские буквы в нижнем регистре, цифры и дефисы',
            'slug.max' => 'SLUG не должен превышать 255 символов',
            'description.required' => 'Описание симулятора обязательно',
            'description.min' => 'Описание должно содержать минимум 20 символов',
            'description.max' => 'Описание не должно превышать 2000 символов',
            'preview_image.image' => 'Файл должен быть изображением',
            'preview_image.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif, webp',
            'preview_image.max' => 'Размер изображения не должен превышать 5MB',
            'is_active.boolean' => 'Статус активности должен быть true или false',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('title') && !$this->has('slug')) {
            $slug = Str::slug($this->title);
            $this->merge([
                'slug' => $slug,
            ]);
        }
    }
}
