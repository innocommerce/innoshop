<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_installer_returns_a_successful_response(): void
    {
        $response = $this->get('/install/');

        $response->assertStatus(200);
    }
}
