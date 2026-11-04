<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the landing page renders successfully after seeding.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('PT Pabrik Gula Candi Baru');
    }
}
