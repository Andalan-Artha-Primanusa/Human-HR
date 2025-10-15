<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Job;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pelamar yang login
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Tidak ada input khusus selain route {job}; validasi bisnis di controller (job open & no duplicate)
        return [];
    }

    public function job(): Job
    {
        return $this->route('job');
    }
}
