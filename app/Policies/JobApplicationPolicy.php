<?php

namespace App\Policies;

use App\Models\User;
use App\Models\JobApplication;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        // Semua user terotentikasi boleh mengakses list (filtering di controller)
        return true;
    }

    public function viewAdmin(User $user): bool
    {
        return $user->hasRole(['admin', 'hr', 'superadmin']);
    }

    public function view(User $user, JobApplication $application): bool
    {
        // Admin/HR bisa lihat semua, Pelamar hanya bisa lihat miliknya
        return $user->hasRole(['admin', 'hr', 'superadmin']) 
               || $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Semua user terotentikasi bisa melamar
        return true;
    }

    public function update(User $user, JobApplication $application): bool
    {
        // Update status/stage khusus Admin/HR
        return $user->hasRole(['admin', 'hr', 'superadmin']);
    }

    public function delete(User $user, JobApplication $application): bool
    {
        // Delete hanya Superadmin
        return $user->hasRole(['superadmin']);
    }

    public function giveFeedback(User $user, JobApplication $application): bool
    {
        // Admin/HR bisa kasih feedback kapan saja
        // Trainer/Karyawan (User) bisa kasih feedback jika relevan
        return $user->hasRole(['admin', 'hr', 'superadmin', 'trainer', 'karyawan']);
    }

    public function sendOffer(User $user, JobApplication $application): bool
    {
        return $user->hasRole(['admin', 'hr', 'superadmin']);
    }

    public function sendMcu(User $user, JobApplication $application): bool
    {
        return $user->hasRole(['admin', 'hr', 'superadmin']);
    }
}
