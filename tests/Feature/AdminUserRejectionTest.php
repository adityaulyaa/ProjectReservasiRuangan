<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserRejectionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);
    }

    private function createPendingUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'role' => Role::USER->value,
            'is_verified' => false,
            'rejected_at' => null,
        ], $attrs));
    }

    // ─── Penolakan Akun ───

    public function test_reject_persists_rejection_status_and_does_not_delete_account(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createPendingUser([
            'name' => 'Calon Ditolak',
            'email' => 'ditolak@kampus.ac.id',
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/reject");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_verified' => false,
        ]);

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->rejected_at, 'rejected_at harus tersimpan');
        $this->assertTrue($fresh->isRejected());
    }

    public function test_rejected_account_is_not_counted_as_pending(): void
    {
        $admin = $this->createAdmin();
        $pending = $this->createPendingUser(['name' => 'Masih Menunggu']);
        $rejected = $this->createPendingUser(['name' => 'Sudah Ditolak']);

        $this->actingAs($admin)->post("/admin/users/{$rejected->id}/reject");

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['unverified'] === 1 // hanya akun yang benar-benar menunggu
                && $stats['rejected'] === 1;
        });

        // Akun yang ditolak tidak muncul saat filter Menunggu Verifikasi
        $pendingFilter = $this->actingAs($admin)->get('/admin/users?status=unverified');
        $pendingFilter->assertSee('Masih Menunggu');
        $pendingFilter->assertDontSee('Sudah Ditolak');
    }

    public function test_cannot_reject_own_account(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post("/admin/users/{$admin->id}/reject")
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_verified);
        $this->assertNull($admin->fresh()->rejected_at);
    }

    // ─── Filter Ditolak ───

    public function test_rejected_filter_lists_only_rejected_accounts(): void
    {
        $admin = $this->createAdmin();
        $rejected = $this->createPendingUser(['name' => 'Akun Ditolak']);
        $pending = $this->createPendingUser(['name' => 'Akun Menunggu']);
        $verified = User::factory()->create(['name' => 'Akun Aktif', 'role' => Role::USER->value, 'is_verified' => true]);

        $this->actingAs($admin)->post("/admin/users/{$rejected->id}/reject");

        $response = $this->actingAs($admin)->get('/admin/users?status=rejected');

        $response->assertOk();
        $response->assertSee('Akun Ditolak');
        $response->assertDontSee('Akun Menunggu');
        $response->assertDontSee('Akun Aktif');
    }

    public function test_rejected_row_shows_rejected_badge_and_restore_not_verify_buttons(): void
    {
        $admin = $this->createAdmin();
        $rejected = $this->createPendingUser(['name' => 'Tampilan Ditolak']);
        $this->actingAs($admin)->post("/admin/users/{$rejected->id}/reject");

        $response = $this->actingAs($admin)->get('/admin/users?status=rejected');

        $response->assertOk();
        $response->assertSee('Ditolak');
        $response->assertSee('Pulihkan');
        $response->assertSee(route('admin.users.restore', $rejected->id), false);
        $response->assertDontSee(route('admin.users.verify', $rejected->id), false);
    }

    // ─── Fitur Pulihkan ───

    public function test_restore_returns_account_to_pending_not_active(): void
    {
        $admin = $this->createAdmin();
        $rejected = $this->createPendingUser(['name' => 'Akan Dipulihkan']);
        $this->actingAs($admin)->post("/admin/users/{$rejected->id}/reject");

        $response = $this->actingAs($admin)->post("/admin/users/{$rejected->id}/restore");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fresh = $rejected->fresh();
        $this->assertFalse($fresh->is_verified, 'Akun harus kembali menunggu, bukan langsung aktif');
        $this->assertNull($fresh->rejected_at);
        $this->assertTrue($fresh->isPending());
    }

    public function test_restored_account_appears_in_pending_list_again(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createPendingUser(['name' => 'Pulih Lagi']);
        $this->actingAs($admin)->post("/admin/users/{$user->id}/reject");
        $this->actingAs($admin)->post("/admin/users/{$user->id}/restore");

        $response = $this->actingAs($admin)->get('/admin/users?status=unverified');

        $response->assertOk();
        $response->assertSee('Pulih Lagi');
    }

    public function test_admin_can_verify_after_restore(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createPendingUser(['name' => 'Verifikasi Setelah Pulih']);
        $this->actingAs($admin)->post("/admin/users/{$user->id}/reject");
        $this->actingAs($admin)->post("/admin/users/{$user->id}/restore");

        $this->actingAs($admin)->post("/admin/users/{$user->id}/verify")
            ->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertTrue($fresh->is_verified);
        $this->assertNull($fresh->rejected_at);
    }

    public function test_restore_fails_for_non_rejected_account(): void
    {
        $admin = $this->createAdmin();
        $pending = $this->createPendingUser(['name' => 'Belum Ditolak']);

        $this->actingAs($admin)->post("/admin/users/{$pending->id}/restore")
            ->assertSessionHas('error');

        $this->assertTrue($pending->fresh()->isPending());
    }

    // ─── Keamanan ───

    public function test_non_admin_cannot_reject_restore_or_verify(): void
    {
        $user = User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);
        $target = $this->createPendingUser(['name' => 'Target']);
        $rejected = $this->createPendingUser(['name' => 'Target Ditolak']);
        $this->actingAs($this->createAdmin())->post("/admin/users/{$rejected->id}/reject");

        $this->actingAs($user)->post("/admin/users/{$target->id}/reject")->assertForbidden();
        $this->actingAs($user)->post("/admin/users/{$rejected->id}/restore")->assertForbidden();
        $this->actingAs($user)->post("/admin/users/{$target->id}/verify")->assertForbidden();
    }

    public function test_staff_cannot_reject_accounts(): void
    {
        $staff = User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);
        $target = $this->createPendingUser();

        $this->actingAs($staff)->post("/admin/users/{$target->id}/reject")->assertForbidden();
        $this->assertTrue($target->fresh()->isPending());
    }

    public function test_rejected_user_cannot_login_or_access_reservation_features(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createPendingUser([
            'email' => 'ditolak.login@kampus.ac.id',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($admin)->post("/admin/users/{$user->id}/reject");

        // Akhiri sesi admin sebelum menguji login akun yang ditolak
        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => 'ditolak.login@kampus.ac.id',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        // Tidak dapat mengakses fitur yang butuh akun aktif
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/reservations/create')->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
    }
}
