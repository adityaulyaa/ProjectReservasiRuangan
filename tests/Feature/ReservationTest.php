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

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function createFacility(array $attributes = []): Facility
    {
        return Facility::create(array_merge([
            'name' => 'Ruang Teater A',
            'type' => 'Auditorium',
            'location' => 'Gedung C Lt.1',
            'capacity' => 50,
            'description' => 'Fasilitas teater lengkap dengan sound system.',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attributes));
    }

    public function test_user_can_view_reservation_create_page(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => now()->addDay()->toDateString(),
            'slots' => '08:00,08:30',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('user.reservations.create');
        $response->assertSee($facility->name);
        $response->assertSee('Ajukan Reservasi Fasilitas');
    }

    public function test_guest_is_redirected_to_login_when_accessing_create_page(): void
    {
        $response = $this->get(route('reservations.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_user_roles_cannot_access_reservation_create(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN->value]);

        $response = $this->actingAs($admin)->get(route('reservations.create'));

        $response->assertStatus(403);
    }

    public function test_user_can_submit_valid_reservation_and_status_is_pending_with_log(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDays(2)->toDateString();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Seminar Teknologi Informasi',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Seminar Teknologi Informasi',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $reservation = Reservation::where('user_id', $user->id)->first();
        $this->assertNotNull($reservation);

        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'actor_id' => $user->id,
            'action' => 'created',
            'new_status' => ReservationStatus::PENDING->value,
        ]);
    }

    public function test_reservation_rejected_if_conflicts_with_approved_reservation(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        // Reservasi approved dari jam 09:00 sampai 11:00
        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'purpose' => 'Rapat Penting',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        // User mencoba reservasi tumpang tindih jam 10:00 sampai 12:00
        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'purpose' => 'Workshop Desain',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseMissing('reservations', [
            'purpose' => 'Workshop Desain',
        ]);
    }

    public function test_reservation_not_blocked_by_pending_reservation(): void
    {
        $user1 = User::factory()->create(['role' => Role::USER->value]);
        $user2 = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        // Reservasi berstatus pending tidak boleh memblokir slot
        Reservation::create([
            'user_id' => $user1->id,
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'purpose' => 'Pengajuan Pertama',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user2)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Pengajuan Kedua',
        ]);

        $response->assertRedirect(route('reservations.index'));
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user2->id,
            'purpose' => 'Pengajuan Kedua',
            'status' => ReservationStatus::PENDING->value,
        ]);
    }

    public function test_reservation_rejected_for_maintenance_facility(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility([
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'purpose' => 'Latihan Tari',
        ]);

        $response->assertSessionHasErrors('facility_id');
        $this->assertDatabaseMissing('reservations', [
            'purpose' => 'Latihan Tari',
        ]);
    }

    public function test_reservation_rejected_if_outside_operational_hours(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        // Di luar jam buka (sebelum 07:00)
        $responseEarly = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '06:00',
            'end_time' => '07:30',
            'purpose' => 'Kegiatan Subuh',
        ]);
        $responseEarly->assertSessionHasErrors('start_time');

        // Di luar jam tutup (setelah 20:00)
        $responseLate = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '19:30',
            'end_time' => '20:30',
            'purpose' => 'Kegiatan Malam',
        ]);
        $responseLate->assertSessionHasErrors('start_time');
    }

    public function test_reservation_rejected_if_not_multiple_of_30_minutes(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:15',
            'end_time' => '09:45',
            'purpose' => 'Rapat Singkat',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    public function test_reservation_rejected_if_date_in_past(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->post(route('reservations.store'), [
            'facility_id' => $facility->id,
            'reservation_date' => now()->subDay()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'purpose' => 'Rapat Kemarin',
        ]);

        $response->assertSessionHasErrors('reservation_date');
    }

    public function test_user_can_view_their_reservation_history_in_index(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $otherUser = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'purpose' => 'Reservasi Pengguna Sendiri',
            'status' => ReservationStatus::PENDING->value,
        ]);

        Reservation::create([
            'user_id' => $otherUser->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'purpose' => 'Reservasi Pengguna Lain',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user)->get(route('reservations.index'));

        $response->assertStatus(200);
        $response->assertSee('Reservasi Pengguna Sendiri');
        $response->assertDontSee('Reservasi Pengguna Lain');
    }

    public function test_user_can_view_their_reservation_detail_with_logs(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Rapat Perencanaan Tahunan',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $reservation->logs()->create([
            'actor_id' => $user->id,
            'action' => 'created',
            'new_status' => 'pending',
            'note' => 'Pengajuan awal reservasi',
        ]);

        $response = $this->actingAs($user)->get(route('reservations.show', $reservation->id));

        $response->assertStatus(200);
        $response->assertSee('Rapat Perencanaan Tahunan');
        $response->assertSee('Pengajuan awal reservasi');
        $response->assertSee($facility->name);
    }

    public function test_user_cannot_view_other_users_reservation_detail(): void
    {
        $user1 = User::factory()->create(['role' => Role::USER->value]);
        $user2 = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user1->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Rapat Rahasia User 1',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user2)->get(route('reservations.show', $reservation->id));

        $response->assertStatus(403);
    }

    public function test_staff_or_admin_can_view_any_reservation_detail(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $staff = User::factory()->create(['role' => Role::STAFF->value]);
        $admin = User::factory()->create(['role' => Role::ADMIN->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Rapat Pengguna Biasa',
            'status' => ReservationStatus::PENDING->value,
        ]);

        // Staff can view
        $responseStaff = $this->actingAs($staff)->get(route('reservations.show', $reservation->id));
        $responseStaff->assertStatus(200);

        // Admin can view
        $responseAdmin = $this->actingAs($admin)->get(route('reservations.show', $reservation->id));
        $responseAdmin->assertStatus(200);
    }

    public function test_user_can_cancel_their_reservation_before_deadline(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Rapat yang akan dibatalkan',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->actingAs($user)->post(route('reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Ada agenda lain yang mendadak',
        ]);

        $response->assertRedirect(route('reservations.show', $reservation->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CANCELLED->value,
            'cancel_reason' => 'Ada agenda lain yang mendadak',
        ]);

        $this->assertDatabaseHas('reservation_logs', [
            'reservation_id' => $reservation->id,
            'actor_id' => $user->id,
            'action' => 'cancelled',
            'new_status' => ReservationStatus::CANCELLED->value,
            'note' => 'Ada agenda lain yang mendadak',
        ]);
    }

    public function test_user_cannot_cancel_reservation_if_within_deadline(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        // Reservasi 1 jam dari sekarang (kurang dari batas 2 jam)
        $dateTime = now()->addHour();

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => $dateTime->toDateString(),
            'start_time' => $dateTime->format('H:i:s'),
            'end_time' => $dateTime->copy()->addHours(2)->format('H:i:s'),
            'purpose' => 'Rapat Mepet',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($user)->post(route('reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Batal mendadak sekali',
        ]);

        $response->assertSessionHasErrors('cancel_reason');

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::APPROVED->value,
        ]);
    }

    public function test_user_cannot_cancel_already_cancelled_or_rejected_reservation(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Rapat Ditolak',
            'status' => ReservationStatus::REJECTED->value,
        ]);

        $response = $this->actingAs($user)->post(route('reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Mau dibatalkan lagi',
        ]);

        $response->assertSessionHasErrors('cancel_reason');
    }

    public function test_user_cannot_cancel_other_users_reservation(): void
    {
        $user1 = User::factory()->create(['role' => Role::USER->value]);
        $user2 = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $reservation = Reservation::create([
            'user_id' => $user1->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Rapat User 1',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($user2)->post(route('reservations.cancel', $reservation->id), [
            'cancel_reason' => 'Mencoba membatalkan milik orang lain',
        ]);

        $response->assertStatus(403);
    }
}
