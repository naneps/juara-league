<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic settings
        Setting::set('maintenance_mode', 'false', 'system', 'boolean');
        Setting::set('registration_enabled', 'true', 'system', 'boolean');
    }

    public function test_public_routes_are_accessible_when_maintenance_is_off(): void
    {
        $response = $this->getJson('/api/v1/tournaments');

        $response->assertStatus(200);
    }

    public function test_public_routes_are_blocked_when_maintenance_is_on(): void
    {
        Setting::set('maintenance_mode', 'true', 'system', 'boolean');

        $response = $this->getJson('/api/v1/tournaments');

        $response->assertStatus(503);
        $response->assertJson([
            'code' => 'MAINTENANCE_MODE'
        ]);
    }

    public function test_admin_can_access_platform_when_maintenance_is_on(): void
    {
        Setting::set('maintenance_mode', 'true', 'system', 'boolean');
        
        // Create role first because it's required by Spatie
        \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->getJson('/api/v1/tournaments');

        $response->assertStatus(200);
    }

    public function test_login_is_accessible_when_maintenance_is_on(): void
    {
        Setting::set('maintenance_mode', 'true', 'system', 'boolean');

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        // Should not be 503, but 401 or 422 depending on auth logic
        $this->assertNotEquals(503, $response->getStatusCode());
    }

    public function test_registration_is_blocked_when_disabled(): void
    {
        Setting::set('registration_enabled', 'false', 'system', 'boolean');

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'code' => 'REGISTRATION_DISABLED'
        ]);
    }
}
