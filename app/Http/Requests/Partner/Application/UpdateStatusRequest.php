<?php

declare(strict_types=1);

namespace App\Http\Requests\Partner\Application;

use App\Models\CaseModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,accepted,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'РЎС‚Р°С‚СѓСЃ РѕР±СЏР·Р°С‚РµР»РµРЅ РґР»СЏ СѓРєР°Р·Р°РЅРёСЏ.',
            'status.in' => 'РќРµРґРѕРїСѓСЃС‚РёРјС‹Р№ СЃС‚Р°С‚СѓСЃ.',
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
