<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PsychotestAttempt;

class SubmitPsychotestRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PsychotestAttempt $attempt */
        $attempt = $this->route('attempt');
        if (!$attempt || !$this->user()) return false;
        
        // Hanya pemilik
        if ($attempt->application->user_id !== $this->user()->id) return false;

        // Cegah submit ulang jika sudah selesai/expired/cancelled
        if (in_array($attempt->status, ['submitted', 'scored', 'expired', 'cancelled'], true)) return false;

        // Expired check
        if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
            $attempt->update(['status' => 'expired']);
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
