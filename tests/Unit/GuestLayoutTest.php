<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\GuestLayout;

class GuestLayoutTest extends TestCase
{
    public function test_render_returns_view(): void
    {
        $component = new GuestLayout();
        $view = $component->render();

        $this->assertInstanceOf(\Illuminate\View\View::class, $view);
        $this->assertEquals('layouts.guest', $view->name());
    }
}
