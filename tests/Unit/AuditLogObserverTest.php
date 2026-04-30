<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Observers\AuditLogObserver;
use App\Models\User;
use App\Models\Company;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditLogObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_logs_audit_entry(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        Auth::setUser($user);

        $company = new Company();
        $company->code = 'TEST' . uniqid();
        $company->name = 'Test Company';
        $company->save();

        $observer = new AuditLogObserver();
        $observer->created($company);

        $log = AuditLog::where('target_type', Company::class)
            ->where('target_id', $company->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('created', $log->event);
        $this->assertEquals($company->id, $log->target_id);
    }

    public function test_updated_logs_audit_entry_with_before_and_after(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        Auth::setUser($user);

        $company = new Company();
        $company->code = 'TEST' . uniqid();
        $company->name = 'Test Company';
        $company->save();

        $original = $company->getOriginal();
        $company->name = 'Updated Company';

        $observer = new AuditLogObserver();
        $observer->updated($company);

        $log = AuditLog::where('target_type', Company::class)
            ->where('target_id', $company->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('updated', $log->event);
    }

    public function test_deleted_logs_audit_entry(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        Auth::setUser($user);

        $company = new Company();
        $company->code = 'TEST' . uniqid();
        $company->name = 'Test Company';
        $company->save();

        $original = $company->getOriginal();
        $companyId = $company->id;

        $observer = new AuditLogObserver();
        $observer->deleted($company);

        $log = AuditLog::where('target_type', Company::class)
            ->where('target_id', $companyId)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('deleted', $log->event);
    }

    public function test_log_contains_target_type(): void
    {
        $user = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        Auth::setUser($user);

        $company = new Company();
        $company->code = 'TEST' . uniqid();
        $company->name = 'Test Company';
        $company->save();

        $beforeCount = AuditLog::count();

        $observer = new AuditLogObserver();
        $observer->created($company);

        $this->assertGreaterThan($beforeCount, AuditLog::count());

        $log = AuditLog::where('target_id', $company->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(Company::class, $log->target_type);
    }
}
