<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['hr','superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:200',
            'mode'         => 'required|in:online,onsite',
            'location'     => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:255',
            'start_at'     => 'required|date',
            'end_at'       => 'required|date|after:start_at',
            'panel'        => 'nullable|array',
            'panel.*.name' => 'required_with:panel|string|max:120',
            'panel.*.email'=> 'required_with:panel|email',
            'notes'        => 'nullable|string',
        ];
    }
}
