<?php

namespace App\Http\Requests\Admin\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? $this->route('company');

        return [
            'code'       => [
                'required','string','max:50',
                Rule::unique('companies','code')->ignore($companyId)->whereNull('deleted_at'),
            ],
            'name'       => ['required','string','max:255'],
            'legal_name' => ['nullable','string','max:255'],
            'email'      => ['nullable','email','max:255'],
            'phone'      => ['nullable','string','max:100'],
            'website'    => ['nullable','url','max:255'],
            'logo_path'  => ['nullable','string','max:255'],
            'address'    => ['nullable','string'],
            'city'       => ['nullable','string','max:100'],
            'province'   => ['nullable','string','max:100'],
            'country'    => ['nullable','string','max:100'],
            'status'     => ['required', Rule::in(['active','inactive'])],
            'meta'       => ['nullable','array'],
        ];
    }
}
