<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/users/me', [
            'name' => 'John Updated',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('John Updated', $user->fresh()->name);
    }

    public function test_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/users/password', [
            'current_password' => 'password123',
            'new_password' => 'NewPassword123',
            'new_password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_update_password_fails_if_current_incorrect()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/users/password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'NewPassword123',
            'new_password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(422);
    }
}
