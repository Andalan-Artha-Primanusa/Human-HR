<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobApplication;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['hr','superadmin']);
    }

    public function view(User $user, JobApplication $application): bool
    {
        return $user->hasAnyRole(['hr','superadmin']) || $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Pelamar boleh buat lamaran
        return $user !== null;
    }

    public function update(User $user, JobApplication $application): bool
    {
        // Update (move stage, set status) khusus HR/superadmin
        return $user->hasAnyRole(['hr','superadmin']);
    }

    public function delete(User $user, JobApplication $application): bool
    {
        return $user->hasAnyRole(['superadmin']);
    }
}
