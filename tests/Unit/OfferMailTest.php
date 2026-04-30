<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\OfferMail;
use App\Models\Offer;
use App\Models\JobApplication;
use App\Models\Job;
use App\Models\User;

class OfferMailTest extends TestCase
{
    public function test_mail_has_offer_property(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $offer = new Offer();
        $offer->setRelation('application', $application);

        $mail = new OfferMail($offer);

        $this->assertEquals($offer, $mail->offer);
    }

    public function test_mail_is_mailable_instance(): void
    {
        $offer = new Offer();
        $mail = new OfferMail($offer);

        $this->assertInstanceOf(\Illuminate\Mail\Mailable::class, $mail);
    }

    public function test_mail_uses_queueable_trait(): void
    {
        $offer = new Offer();
        $mail = new OfferMail($offer);

        $this->assertTrue(method_exists($mail, 'queue'));
    }
}
