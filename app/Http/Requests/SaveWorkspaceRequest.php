<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preset' => ['required', 'string', 'in:institution_organization,operations_logistics,finance_audit,personal_customer,minimal_focus'],
            'layout' => ['required', 'array'],
            'layout.*.id' => ['required', 'string'],
            'layout.*.col' => ['required', 'integer', 'min:1', 'max:12'],
            'layout.*.visible' => ['required', 'boolean'],
        ];
    }
}
