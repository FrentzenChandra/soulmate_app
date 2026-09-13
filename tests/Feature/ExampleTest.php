<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_storage_fallback_route_serves_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/sample.jpg', 'dummy-image-content');

        $response = $this->get('/storage/products/sample.jpg');
        $response->assertStatus(200);

        $missingResponse = $this->get('/storage/products/non_existent.jpg');
        $missingResponse->assertStatus(404);
    }
}
