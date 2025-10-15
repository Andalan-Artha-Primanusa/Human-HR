<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['hr','superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'gross_salary' => 'required|numeric|min:0',
            'allowance'    => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
            'html'         => 'nullable|string',
        ];
    }
}
