<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        
        $response = $this->actingAs($customer)->getJson('/api/admin/tags');
        
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_web_admin_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin/reports');

        $response->assertStatus(403);
    }
}
