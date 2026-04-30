<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Providers\EventServiceProvider;
use Illuminate\Auth\Events\Registered;

class EventServiceProviderTest extends TestCase
{
    public function test_should_not_discover_events(): void
    {
        $provider = new EventServiceProvider(app());
        $this->assertFalse($provider->shouldDiscoverEvents());
    }

    public function test_listens_for_registered_event(): void
    {
        $provider = new EventServiceProvider(app());
        $events = $provider->listens();

        $this->assertArrayHasKey(Registered::class, $events);
    }

    public function test_registered_event_sends_verification_notification(): void
    {
        $provider = new EventServiceProvider(app());
        $events = $provider->listens();

        $listeners = $events[Registered::class];
        $this->assertContains(
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
            $listeners
        );
    }
}
