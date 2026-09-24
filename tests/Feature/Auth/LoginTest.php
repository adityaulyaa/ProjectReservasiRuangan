<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_verified_user_can_authenticate_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_admin_can_authenticate_and_redirect_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_unverified_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertEquals('Akun Anda belum diverifikasi admin.', session('errors')->first('email'));
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
