<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function createFacility(array $attributes = []): Facility
    {
        return Facility::create(array_merge([
            'name' => 'Ruang Diskusi A',
            'type' => 'Ruang Diskusi',
            'location' => 'Gedung A Lt.2',
            'capacity' => 15,
            'description' => 'Fasilitas diskusi lengkap dengan proyektor.',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attributes));
    }

    public function test_public_user_can_view_facility_availability_page(): void
    {
        $facility = $this->createFacility();

        $response = $this->get(route('facilities.availability', $facility->id));

        $response->assertStatus(200);
        $response->assertViewIs('public.facility-availability');
        $response->assertViewHas('facility');
        $response->assertViewHas('slotStatuses');
        $response->assertSee($facility->name);
        $response->assertSee($facility->location);
    }

    public function test_availability_page_displays_all_operational_time_slots(): void
    {
        $facility = $this->createFacility();

        $response = $this->get(route('facilities.availability', $facility->id));

        $response->assertStatus(200);
        $slotStatuses = $response->viewData('slotStatuses');

        // Jam 07:00 s/d 20:00 (slot 30 menit) -> 26 slots
        $this->assertCount(26, $slotStatuses);
        $this->assertEquals('07:00', $slotStatuses[0]['start']);
        $this->assertEquals('07:30', $slotStatuses[0]['end']);
        $this->assertEquals('19:30', $slotStatuses[25]['start']);
        $this->assertEquals('20:00', $slotStatuses[25]['end']);
    }

    public function test_approved_reservations_are_marked_as_occupied(): void
    {
        $facility = $this->createFacility();
        $user = User::factory()->create();
        $date = now()->addDay()->toDateString();

        Reservation::create([
            'facility_id' => $facility->id,
            'user_id' => $user->id,
            'reservation_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'purpose' => 'Rapat Koordinasi BEM',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->get(route('facilities.availability', [
            'id' => $facility->id,
            'date' => $date,
        ]));

        $response->assertStatus(200);
        $slotStatuses = collect($response->viewData('slotStatuses'))->keyBy('start');

        // Slot 09:00 - 09:30, 09:30 - 10:00, 10:00 - 10:30 harus terbooking
        $this->assertTrue($slotStatuses['09:00']['is_occupied']);
        $this->assertTrue($slotStatuses['09:30']['is_occupied']);
        $this->assertTrue($slotStatuses['10:00']['is_occupied']);

        // Slot sebelum dan sesudah harus tetap tersedia
        $this->assertFalse($slotStatuses['08:30']['is_occupied']);
        $this->assertFalse($slotStatuses['10:30']['is_occupied']);

        $response->assertSee('Terbooking');
    }

    public function test_pending_reservations_do_not_block_slots(): void
    {
        $facility = $this->createFacility();
        $user = User::factory()->create();
        $date = now()->addDay()->toDateString();

        // Reservasi pending tidak boleh memblokir slot
        Reservation::create([
            'facility_id' => $facility->id,
            'user_id' => $user->id,
            'reservation_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Latihan Presentasi Kelompok',
            'status' => ReservationStatus::PENDING->value,
        ]);

        $response = $this->get(route('facilities.availability', [
            'id' => $facility->id,
            'date' => $date,
        ]));

        $response->assertStatus(200);
        $slotStatuses = collect($response->viewData('slotStatuses'))->keyBy('start');

        $this->assertFalse($slotStatuses['09:00']['is_occupied']);
        $this->assertFalse($slotStatuses['09:30']['is_occupied']);
    }

    public function test_privacy_compliance_no_applicant_or_purpose_details_shown(): void
    {
        $facility = $this->createFacility();
        $user = User::factory()->create(['name' => 'Fauzan Rahasia Super']);
        $date = now()->addDay()->toDateString();

        Reservation::create([
            'facility_id' => $facility->id,
            'user_id' => $user->id,
            'reservation_date' => $date,
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'purpose' => 'Rapat Sangat Rahasia Organisasi',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->get(route('facilities.availability', [
            'id' => $facility->id,
            'date' => $date,
        ]));

        $response->assertStatus(200);
        // Privasi: Nama pemohon dan tujuan peminjaman TIDAK BOLEH tampil di halaman publik ketersediaan
        $response->assertDontSee('Fauzan Rahasia Super');
        $response->assertDontSee('Rapat Sangat Rahasia Organisasi');
    }

    public function test_maintenance_facility_shows_maintenance_warning(): void
    {
        $facility = $this->createFacility([
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

        $response = $this->get(route('facilities.availability', $facility->id));

        $response->assertStatus(200);
        $response->assertSee('Dalam Perbaikan');
        $response->assertSee('Fasilitas Sedang Dalam Perbaikan');
        $response->assertSee('Fasilitas Tidak Dapat Dipesan');
    }

    public function test_inactive_facility_returns_404(): void
    {
        $facility = $this->createFacility([
            'status' => FacilityStatus::INACTIVE->value,
        ]);

        $response = $this->get(route('facilities.availability', $facility->id));

        $response->assertStatus(404);
    }

    public function test_unauthenticated_guest_sees_login_prompt_instead_of_reservation_submit(): void
    {
        $facility = $this->createFacility();

        $response = $this->get(route('facilities.availability', $facility->id));

        $response->assertStatus(200);
        $response->assertSee('Mode Lihat Jadwal');
        $response->assertSee('Perlu Masuk Akun');
        $response->assertSee('Masuk untuk Reservasi');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertDontSee('Lanjutkan reservasi');
    }

    public function test_authenticated_user_sees_continue_reservation_button(): void
    {
        $facility = $this->createFacility();
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('facilities.availability', $facility->id));

        $response->assertStatus(200);
        $response->assertDontSee('Mode Lihat Jadwal');
        $response->assertDontSee('Perlu Masuk Akun');
        $response->assertSee('Lanjutkan reservasi');
    }
}
