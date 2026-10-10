<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_shows_only_owned_reservations_and_reports(): void
    {
        $user = $this->createUser('user', true);
        $otherUser = $this->createUser('user', true);
        $facility = $this->createFacility();

        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'purpose' => 'Rapat user',
            'status' => 'pending',
        ]);

        Reservation::create([
            'user_id' => $otherUser->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'purpose' => 'Rapat user lain',
            'status' => 'approved',
        ]);

        Report::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'category' => 'it',
            'description' => 'Komputer rusak',
            'status' => 'new',
        ]);

        Report::create([
            'user_id' => $otherUser->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'AC rusak',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['total_reservations'] === 1
                && $stats['pending_reservations'] === 1
                && $stats['approved_reservations'] === 0
                && $stats['total_reports'] === 1
                && $stats['new_reports'] === 1
                && $stats['resolved_reports'] === 0;
        });
        $response->assertViewHas('recentReservations');
        $response->assertViewHas('recentReports');
    }

    public function test_staff_dashboard_shows_queue_counts_and_previews(): void
    {
        $staff = $this->createUser('staff', true);
        $user = $this->createUser('user', true);
        $facility = $this->createFacility();

        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'purpose' => 'Rapat',
            'status' => 'pending',
        ]);

        Report::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'category' => 'listrik',
            'description' => 'Lampu mati',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($staff)->get('/staff/dashboard');

        $response->assertOk();
        $response->assertViewHas('pendingReservations', 1);
        $response->assertViewHas('inProgressReports', 1);
        $response->assertViewHas('recentPendingReservations');
        $response->assertViewHas('recentNewReports');
    }

    public function test_admin_dashboard_shows_totals_report_status_and_top_facilities(): void
    {
        $admin = $this->createUser('admin', true);
        $user = $this->createUser('user', true);
        $facility = $this->createFacility();

        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'purpose' => 'Rapat',
            'status' => 'approved',
        ]);

        Report::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'category' => 'furniture',
            'description' => 'Kursi rusak',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['total_facilities'] === 1
                && $stats['total_users'] === 2
                && $stats['reservations_today'] === 1
                && $stats['approved_reservations'] === 1
                && $stats['rejected_reports'] === 1;
        });
        $response->assertViewHas('topFacilities');
    }

    private function createUser(string $role, bool $verified): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_verified' => $verified,
        ]);
    }

    private function createFacility(array $attributes = []): Facility
    {
        return Facility::create(array_merge([
            'name' => 'Ruang Test',
            'type' => 'Classroom',
            'location' => 'Gedung Test',
            'capacity' => 30,
            'description' => 'Fasilitas untuk pengujian',
            'status' => 'active',
        ], $attributes));
    }
}
