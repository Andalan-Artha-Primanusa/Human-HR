<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Job;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin list via panel
        return $user->hasAnyRole(['hr','superadmin']);
    }

    public function view(?User $user, Job $job): bool
    {
        // Publik boleh lihat job open; admin boleh semua
        if ($user && $user->hasAnyRole(['hr','superadmin'])) return true;
        return $job->status === 'open';
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['hr','superadmin']);
    }

    public function update(User $user, Job $job): bool
    {
        return $user->hasAnyRole(['hr','superadmin']);
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->hasAnyRole(['superadmin']);
    }
}
