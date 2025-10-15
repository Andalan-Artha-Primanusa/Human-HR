<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['hr','superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'code'            => 'required|string|max:50|unique:jobs,code',
            'title'           => 'required|string|max:200',
            'site_code'       => 'nullable|string|max:50',
            'division'        => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:100',
            'employment_type' => 'required|in:intern,contract,fulltime',
            'openings'        => 'required|integer|min:1',
            'status'          => 'required|in:draft,open,closed',
            'description'     => 'nullable|string',
        ];
    }
}
