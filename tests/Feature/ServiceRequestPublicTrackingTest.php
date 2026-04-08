<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestPublicTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_page_returns_404_for_unknown_token(): void
    {
        $this->get('/track/SR-DEADBEEFDEADBEEFDEADBEEFDEADBEEF')
            ->assertNotFound();
    }

    public function test_track_page_returns_404_for_short_token(): void
    {
        $this->get('/track/short')
            ->assertNotFound();
    }
}
