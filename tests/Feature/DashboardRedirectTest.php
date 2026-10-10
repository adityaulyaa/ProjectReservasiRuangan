<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_accessing_dashboard_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_accessing_dashboard_redirects_to_staff_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($staff)->get('/dashboard');

        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_user_accessing_dashboard_shows_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_admin_can_access_admin_dashboard_directly(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_staff_can_access_staff_dashboard_directly(): void
    {
        $staff = User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertStatus(200);
    }
}
