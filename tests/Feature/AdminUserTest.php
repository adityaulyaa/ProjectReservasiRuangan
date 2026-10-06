<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);
    }

    private function createStaff(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ], $attrs));
    }

    private function createUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => Role::USER->value,
            'is_verified' => true,
        ], $attrs));
    }

    // ─── Hak Akses & Middleware ───

    public function test_guest_cannot_access_admin_users(): void
    {
        $response = $this->get('/admin/users');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_users(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_staff_cannot_access_admin_users(): void
    {
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get('/admin/users');
        $response->assertStatus(403);
    }

    // ─── Index & Pencarian ───

    public function test_admin_can_view_users_index(): void
    {
        $admin = $this->createAdmin();
        $user1 = $this->createUser(['name' => 'Aditya Pratama']);
        $user2 = $this->createUser(['name' => 'Bambang Sudirman']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
        $response->assertSee('Aditya Pratama');
        $response->assertSee('Bambang Sudirman');
    }

    public function test_admin_can_search_and_filter_users_by_role_and_status(): void
    {
        $admin = $this->createAdmin();
        $verifiedStaff = $this->createStaff(['name' => 'Petugas Fasilitas 1', 'is_verified' => true]);
        $unverifiedUser = $this->createUser(['name' => 'Mahasiswa Baru Unverified', 'is_verified' => false]);

        // Filter role staff
        $staffFilter = $this->actingAs($admin)->get('/admin/users?role=staff');
        $staffFilter->assertStatus(200);
        $staffFilter->assertSee('Petugas Fasilitas 1');
        $staffFilter->assertDontSee('Mahasiswa Baru Unverified');

        // Filter status unverified
        $unverifiedFilter = $this->actingAs($admin)->get('/admin/users?status=unverified');
        $unverifiedFilter->assertStatus(200);
        $unverifiedFilter->assertSee('Mahasiswa Baru Unverified');
        $unverifiedFilter->assertDontSee('Petugas Fasilitas 1');

        // Search by name
        $searchResponse = $this->actingAs($admin)->get('/admin/users?search=Baru');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Mahasiswa Baru Unverified');
        $searchResponse->assertDontSee('Petugas Fasilitas 1');
    }

    // ─── Create & Store (Direct Registration by Admin) ───

    public function test_admin_can_view_create_user_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
        // Tidak menampilkan option peran administrator di select
        $response->assertDontSee('<option value="admin">', false);
    }

    public function test_admin_can_store_user_and_it_is_automatically_verified(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Mahasiswa Langsung Aktif',
            'email' => 'mahasiswa.langsung@kampus.ac.id',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->actingAs($admin)->post('/admin/users', $payload);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Mahasiswa Langsung Aktif',
            'email' => 'mahasiswa.langsung@kampus.ac.id',
            'role' => 'user',
            'is_verified' => true, // Langsung aktif saat dibuat admin
        ]);
    }

    public function test_admin_can_store_staff_and_it_is_automatically_verified(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Petugas Ruang Lab',
            'email' => 'petugas.lab@kampus.ac.id',
            'password' => 'password123',
            'role' => 'staff',
        ];

        $response = $this->actingAs($admin)->post('/admin/users', $payload);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Petugas Ruang Lab',
            'email' => 'petugas.lab@kampus.ac.id',
            'role' => 'staff',
            'is_verified' => true,
        ]);
    }

    public function test_store_user_validation_rules(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createUser(['email' => 'duplikat@kampus.ac.id']);

        // Data kosong & duplikat
        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => '',
            'email' => 'duplikat@kampus.ac.id',
            'password' => 'short',
            'role' => 'admin', // Admin tidak diperbolehkan dibuat via form
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    // ─── Verifikasi & Penolakan Akun (Verify & Reject) ───

    public function test_admin_can_verify_unverified_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser([
            'name' => 'Pendaftar Mandiri',
            'email' => 'mandiri@kampus.ac.id',
            'is_verified' => false,
        ]);

        $this->assertFalse($user->is_verified);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/verify");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_admin_can_reject_or_suspend_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser([
            'name' => 'User Tertunda',
            'email' => 'tertunda@kampus.ac.id',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/reject");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse($user->fresh()->is_verified);
    }

    // ─── Edit & Update User ───

    public function test_admin_can_view_edit_user_page(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['name' => 'User Untuk Diedit']);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertSee('User Untuk Diedit');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser([
            'name' => 'Nama Lama',
            'role' => 'user',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Nama Baru Diperbarui',
            'email' => $user->email,
            'role' => 'staff',
            'is_verified' => 1,
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $freshUser = $user->fresh();
        $this->assertEquals('Nama Baru Diperbarui', $freshUser->name);
        $this->assertEquals(Role::STAFF, $freshUser->role);
        $this->assertTrue($freshUser->is_verified);
    }
}
