<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffReservationTest extends TestCase
{
    use RefreshDatabase;

    private function createStaff(): User
    {
        return User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create([
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);
    }

    private function createFacility(array $attrs = []): Facility
    {
        return Facility::create(array_merge([
            'name' => 'Ruang Rapat A',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung A Lt.2',
            'capacity' => 30,
            'description' => 'Ruang rapat dilengkapi proyektor.',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attrs));
    }

    private function createReservation(array $attrs = []): Reservation
    {
        $user = $attrs['user_id'] ?? $this->createUser()->id;
        $facility = $attrs['facility_id'] ?? $this->createFacility()->id;

        return Reservation::create(array_merge([
            'user_id' => $user,
            'facility_id' => $facility,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'purpose' => 'Rapat koordinasi mingguan',
            'status' => ReservationStatus::PENDING->value,
        ], $attrs));
    }

    // ─── Queue Page ───

    public function test_staff_can_view_reservation_queue(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation();

        $response = $this->actingAs($staff)->get(route('staff.reservations.queue'));

        $response->assertStatus(200);
        $response->assertSee($reservation->facility->name);
    }

    public function test_non_staff_cannot_access_queue(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('staff.reservations.queue'));

        $response->assertStatus(403);
    }

    public function test_queue_filters_by_status(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility();

        $pending = $this->createReservation([
            'facility_id' => $facility->id,
            'status' => ReservationStatus::PENDING->value,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $approved = $this->createReservation([
            'facility_id' => $facility->id,
            'status' => ReservationStatus::APPROVED->value,
            'start_time' => '11:00',
            'end_time' => '12:00',
        ]);

        $response = $this->actingAs($staff)->get(route('staff.reservations.queue', ['status' => 'pending']));
        $response->assertStatus(200);
    }

    // ─── Approve ───

    public function test_staff_can_approve_pending_reservation(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation();

        $response = $this->actingAs($staff)->post(route('staff.reservations.approve', $reservation->id));

        $response->assertRedirect(route('staff.reservations.queue'));
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals(ReservationStatus::APPROVED->value, $reservation->status->value);
        $this->assertEquals($staff->id, $reservation->processed_by);

        // Log tercatat
        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'action' => 'approved',
            'actor_id' => $staff->id,
        ]);
    }

    public function test_approve_fails_when_conflict_exists(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        // Create approved reservation occupying the slot
        $this->createReservation([
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        // Create a pending reservation in the same slot
        $pending = $this->createReservation([
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.approve', $pending->id));

        $response->assertSessionHasErrors('conflict');

        $pending->refresh();
        $this->assertEquals(ReservationStatus::PENDING->value, $pending->status->value);
    }

    public function test_approve_fails_for_non_pending_reservation(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation([
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.approve', $reservation->id));

        $response->assertSessionHasErrors('status');
    }

    public function test_approve_fails_for_maintenance_facility(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $reservation = $this->createReservation([
            'facility_id' => $facility->id,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.approve', $reservation->id));

        $response->assertSessionHasErrors('facility');
    }

    // ─── Reject ───

    public function test_staff_can_reject_pending_reservation(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation();

        $response = $this->actingAs($staff)->post(route('staff.reservations.reject', $reservation->id), [
            'reject_reason' => 'Fasilitas sedang direnovasi untuk bulan depan.',
        ]);

        $response->assertRedirect(route('staff.reservations.queue'));
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals(ReservationStatus::REJECTED->value, $reservation->status->value);
        $this->assertEquals('Fasilitas sedang direnovasi untuk bulan depan.', $reservation->reject_reason);
        $this->assertEquals($staff->id, $reservation->processed_by);

        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'action' => 'rejected',
            'actor_id' => $staff->id,
        ]);
    }

    public function test_reject_requires_reason(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation();

        $response = $this->actingAs($staff)->post(route('staff.reservations.reject', $reservation->id), [
            'reject_reason' => '',
        ]);

        $response->assertSessionHasErrors('reject_reason');
    }

    public function test_reject_fails_for_non_pending(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation([
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.reject', $reservation->id), [
            'reject_reason' => 'Alasan penolakan.',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Cancel Forced ───

    public function test_staff_can_cancel_approved_reservation(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation([
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Keadaan darurat gedung, perlu evakuasi.',
        ]);

        $response->assertRedirect(route('staff.reservations.queue'));
        $response->assertSessionHas('success');

        $reservation->refresh();
        $this->assertEquals(ReservationStatus::CANCELLED->value, $reservation->status->value);
        $this->assertEquals('Keadaan darurat gedung, perlu evakuasi.', $reservation->cancel_reason);

        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'action' => 'cancelled_forced',
            'actor_id' => $staff->id,
        ]);
    }

    public function test_cancel_forced_requires_reason(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation([
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.cancel', $reservation->id), [
            'cancel_reason' => '',
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_cancel_forced_fails_for_pending(): void
    {
        $staff = $this->createStaff();
        $reservation = $this->createReservation([
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Alasan darurat.',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Slot Lock Verification ───

    public function test_approved_reservation_locks_slot(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        // First reservation: approve
        $first = $this->createReservation([
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $this->actingAs($staff)->post(route('staff.reservations.approve', $first->id));
        $first->refresh();
        $this->assertEquals(ReservationStatus::APPROVED->value, $first->status->value);

        // Second reservation: same slot → conflict
        $second = $this->createReservation([
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:30',
            'end_time' => '10:30',
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reservations.approve', $second->id));
        $response->assertSessionHasErrors('conflict');

        $second->refresh();
        $this->assertEquals(ReservationStatus::PENDING->value, $second->status->value);
    }
}
