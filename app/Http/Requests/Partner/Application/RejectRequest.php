<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use App\Models\CaseModel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'РЈРєР°Р¶РёС‚Рµ РїСЂРёС‡РёРЅСѓ РѕС‚РєР»РѕРЅРµРЅРёСЏ Р·Р°СЏРІРєРё.',
            'rejection_reason.min' => 'РџСЂРёС‡РёРЅР° РѕС‚РєР»РѕРЅРµРЅРёСЏ РґРѕР»Р¶РЅР° СЃРѕРґРµСЂР¶Р°С‚СЊ РјРёРЅРёРјСѓРј 10 СЃРёРјРІРѕР»РѕРІ.',
            'rejection_reason.max' => 'РџСЂРёС‡РёРЅР° РѕС‚РєР»РѕРЅРµРЅРёСЏ РЅРµ РґРѕР»Р¶РЅР° РїСЂРµРІС‹С€Р°С‚СЊ 1000 СЃРёРјРІРѕР»РѕРІ.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $case = $this->route('case');
            if ($case instanceof CaseModel && $case->deadline && $case->deadline->isPast()) {
                $validator->errors()->add('case', 'РќРµР»СЊР·СЏ РёР·РјРµРЅСЏС‚СЊ Р·Р°СЏРІРєРё РїРѕСЃР»Рµ РґРµРґР»Р°Р№РЅР° РєРµР№СЃР°.');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        $case = $this->route('case');
        $exception = new ValidationException($validator);

        if (! $this->header('X-Inertia') && $case) {
            $exception->redirectTo(route('partner.cases.show', $case));
        }

        throw $exception;
    }
}
