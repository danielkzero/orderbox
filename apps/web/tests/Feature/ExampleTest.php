<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_api_health_endpoint_reports_process_health(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_api_ready_endpoint_reports_database_health(): void
    {
        $this->getJson('/api/v1/ready')
            ->assertOk()
            ->assertJsonPath('data.status', 'ready');
    }
}
