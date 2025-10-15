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
        return $attempt && $this->user() && $attempt->application->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array',
        ];
    }
}
