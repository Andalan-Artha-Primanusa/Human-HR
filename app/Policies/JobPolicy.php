<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Job;

class JobPolicy
{
    /** helper kecil */
    private function isAdmin(?User $user): bool
    {
        return $user && in_array($user->role, ['hr','superadmin'], true);
    }

    private function isSuperAdmin(?User $user): bool
    {
        return $user && $user->role === 'superadmin';
    }

    public function viewAny(User $user): bool
    {
        // Admin list via panel
        return $this->isAdmin($user);
    }

    /**
     * Catatan: Laravel biasanya expects User $user (non-null).
     * Kamu pakai ?User di sini supaya guest bisa lolos untuk job "open".
     * Pastikan mapping policy-nya sesuai (atau method ini tidak dipakai untuk route publik).
     */
    public function view(?User $user, Job $job): bool
    {
        // Admin boleh semua
        if ($this->isAdmin($user)) return true;

        // Guest / user biasa: hanya job open
        return $job->status === 'open';
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Job $job): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Job $job): bool
    {
        return $this->isSuperAdmin($user);
    }

    // (opsional) restore/forceDelete kalau dipakai:
    public function restore(User $user, Job $job): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function forceDelete(User $user, Job $job): bool
    {
        return $this->isSuperAdmin($user);
    }
}
