<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['hr','superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'to_stage' => 'required|in:applied,psychotest,hr_iv,user_iv,final,offer,hired,rejected',
            'status'   => 'nullable|in:pending,passed,failed,no-show,reschedule',
            'note'     => 'nullable|string',
            'score'    => 'nullable|numeric',
        ];
    }
}
