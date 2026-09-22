<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_can_be_accessed_without_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->get('/facilities');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_to_login_for_protected_routes(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/staff/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_staff_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get('/staff/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_staff_can_access_staff_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($staff)->get('/staff/dashboard');
        $response->assertStatus(200);
    }

    public function test_user_can_access_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
