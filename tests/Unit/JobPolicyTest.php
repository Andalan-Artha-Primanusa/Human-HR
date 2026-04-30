<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Policies\JobPolicy;
use App\Models\User;
use App\Models\Job;

class JobPolicyTest extends TestCase
{
    public function test_view_any_allows_hr(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'hr';

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_any_allows_superadmin(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'superadmin';

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_any_denies_pelamar(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'pelamar';

        $this->assertFalse($policy->viewAny($user));
    }

    public function test_view_allows_admin_for_any_job(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'hr';

        $job = new Job();
        $job->status = 'closed';

        $this->assertTrue($policy->view($user, $job));
    }

    public function test_view_allows_guest_for_open_job(): void
    {
        $policy = new JobPolicy();

        $job = new Job();
        $job->status = 'open';

        $this->assertTrue($policy->view(null, $job));
    }

    public function test_view_denies_guest_for_closed_job(): void
    {
        $policy = new JobPolicy();

        $job = new Job();
        $job->status = 'closed';

        $this->assertFalse($policy->view(null, $job));
    }

    public function test_view_allows_pelamar_for_open_job(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'pelamar';

        $job = new Job();
        $job->status = 'open';

        $this->assertTrue($policy->view($user, $job));
    }

    public function test_view_denies_pelamar_for_closed_job(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'pelamar';

        $job = new Job();
        $job->status = 'closed';

        $this->assertFalse($policy->view($user, $job));
    }

    public function test_create_allows_admin(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'hr';

        $this->assertTrue($policy->create($user));
    }

    public function test_create_denies_non_admin(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'pelamar';

        $this->assertFalse($policy->create($user));
    }

    public function test_update_allows_admin(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'superadmin';

        $job = new Job();

        $this->assertTrue($policy->update($user, $job));
    }

    public function test_update_denies_non_admin(): void
    {
        $policy = new JobPolicy();
        $user = new User();
        $user->role = 'pelamar';

        $job = new Job();

        $this->assertFalse($policy->update($user, $job));
    }

    public function test_delete_allows_only_superadmin(): void
    {
        $policy = new JobPolicy();
        $superadmin = new User();
        $superadmin->role = 'superadmin';

        $hr = new User();
        $hr->role = 'hr';

        $job = new Job();

        $this->assertTrue($policy->delete($superadmin, $job));
        $this->assertFalse($policy->delete($hr, $job));
    }

    public function test_restore_allows_only_superadmin(): void
    {
        $policy = new JobPolicy();
        $superadmin = new User();
        $superadmin->role = 'superadmin';

        $hr = new User();
        $hr->role = 'hr';

        $job = new Job();

        $this->assertTrue($policy->restore($superadmin, $job));
        $this->assertFalse($policy->restore($hr, $job));
    }

    public function test_force_delete_allows_only_superadmin(): void
    {
        $policy = new JobPolicy();
        $superadmin = new User();
        $superadmin->role = 'superadmin';

        $hr = new User();
        $hr->role = 'hr';

        $job = new Job();

        $this->assertTrue($policy->forceDelete($superadmin, $job));
        $this->assertFalse($policy->forceDelete($hr, $job));
    }
}
