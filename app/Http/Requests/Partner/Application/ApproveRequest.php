<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use App\Models\CaseModel;
use Illuminate\Foundation\Http\FormRequest;

class ApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.max' => 'РљРѕРјРјРµРЅС‚Р°СЂРёР№ РЅРµ РґРѕР»Р¶РµРЅ РїСЂРµРІС‹С€Р°С‚СЊ 1000 СЃРёРјРІРѕР»РѕРІ.',
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
}
