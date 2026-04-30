<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\McuTemplate;

class McuTemplateTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $template = new McuTemplate();
        $this->assertContains('name', $template->getFillable());
        $this->assertContains('company_name', $template->getFillable());
        $this->assertContains('city', $template->getFillable());
        $this->assertContains('project_name', $template->getFillable());
        $this->assertContains('vendor_name', $template->getFillable());
        $this->assertContains('vendor_address', $template->getFillable());
        $this->assertContains('subject', $template->getFillable());
        $this->assertContains('for_text', $template->getFillable());
        $this->assertContains('bu_name', $template->getFillable());
        $this->assertContains('owner_name', $template->getFillable());
        $this->assertContains('matrix_owner', $template->getFillable());
        $this->assertContains('notes', $template->getFillable());
        $this->assertContains('result_emails', $template->getFillable());
        $this->assertContains('signer_name', $template->getFillable());
        $this->assertContains('signer_title', $template->getFillable());
        $this->assertContains('footer_company_name', $template->getFillable());
        $this->assertContains('footer_address', $template->getFillable());
        $this->assertContains('footer_email', $template->getFillable());
        $this->assertContains('footer_website', $template->getFillable());
        $this->assertContains('is_active', $template->getFillable());
    }
}
