<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'hr', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:200'],
            'division' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', 'in:intern,contract,fulltime'],
            'status' => ['required', 'in:draft,open,closed'],
            'description' => ['nullable', 'string'],
            'skills' => ['nullable'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'site_id' => ['nullable', 'uuid', 'exists:sites,id', 'required_without:site_code'],
            'site_code' => ['nullable', 'string', 'exists:sites,code', 'required_without:site_id'],
            'company_id' => ['nullable', 'uuid', 'exists:companies,id', 'prohibits:company_code'],
            'company_code' => ['nullable', 'string', 'exists:companies,code', 'prohibits:company_id'],
        ];
    }
}
