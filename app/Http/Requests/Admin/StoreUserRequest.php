<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Авторизация должна проверяться в контроллере через middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kubgtu_id' => 'nullable|string|unique:users,kubgtu_id|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'course' => 'nullable|integer|min:1|max:6',
            'role' => 'required|in:admin,teacher,student,partner',        ];
    }
}
