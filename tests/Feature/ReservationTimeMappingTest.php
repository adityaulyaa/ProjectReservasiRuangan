<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReservationTimeMappingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createFacility(array $attributes = []): Facility
    {
        return Facility::create(array_merge([
            'name' => 'Ruang Uji Waktu',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Z Lt.1',
            'capacity' => 10,
            'description' => 'Fasilitas uji pemetaan waktu.',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attributes));
    }

    /**
     * Slot dari kalender harus persis terbawa ke view create.
     */
    #[DataProvider('slotSelections')]
    public function test_selected_slot_is_carried_into_create_page(string $slots, string $expectedStart, string $expectedEnd): void
    {
        // Waktu "sekarang" lebih malam dari slot pagi, agar bug auto-override
        // (yang mengganti slot pagi ke slot berikutnya) ikut terpicu.
        Carbon::setTestNow(Carbon::create(2026, 10, 10, 5, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => $date,
            'slots' => $slots,
        ]));

        $response->assertOk();
        $response->assertViewHas('startTime', $expectedStart);
        $response->assertViewHas('endTime', $expectedEnd);
        $response->assertViewHas('date', $date);
        $response->assertViewHas('facilityId', (string) $facility->id);
    }

    public static function slotSelections(): array
    {
        return [
            'pagi 1 slot' => ['07:00', '07:00', '07:30'],
            'pagi 2 slot' => ['07:00,07:30', '07:00', '08:00'],
            'siang 1 slot' => ['11:30', '11:30', '12:00'],
            'sore 3 slot' => ['15:00,15:30,16:00', '15:00', '16:30'],
            'malam 1 slot' => ['19:30', '19:30', '20:00'],
        ];
    }

    public function test_explicit_selection_is_flagged_and_not_treated_as_default(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        $withSlots = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => $date,
            'slots' => '07:00',
        ]));
        $withSlots->assertViewHas('hasExplicitSelection', true);

        $withoutSlots = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => $date,
        ]));
        $withoutSlots->assertViewHas('hasExplicitSelection', false);
    }

    public function test_occupied_slots_are_passed_to_create_page(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Sudah disetujui',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => $date,
        ]));

        $response->assertOk();
        $booked = $response->viewData('bookedSlots');
        $this->assertContains('09:00', $booked);
        $this->assertContains('09:30', $booked);
        $this->assertNotContains('10:00', $booked);
    }

    public function test_create_page_marks_explicit_selection_and_preserves_range_in_source(): void
    {
        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => now()->addDay()->toDateString(),
            'slots' => '08:00,08:30',
        ]));

        $response->assertOk();
        // Nilai awal yang dirender harus 08:00 & 09:00 (dua slot dari pilihan user)
        $response->assertSee("startTime: '08:00'", false);
        $response->assertSee("endTime: '09:00'", false);
        // Penanda eksplisit + guard pada init
        $response->assertSee('hasExplicitSelection: true', false);
    }

    public function test_booked_slot_selection_is_surfaced_on_create_page(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 10, 5, 0, 0, 'Asia/Jakarta'));

        $user = User::factory()->create(['role' => Role::USER->value]);
        $facility = $this->createFacility();
        $date = now()->addDay()->toDateString();

        // Slot 07:00 - 07:30 sudah disetujui
        Reservation::create([
            'user_id' => User::factory()->create()->id,
            'facility_id' => $facility->id,
            'reservation_date' => $date,
            'start_time' => '07:00:00',
            'end_time' => '07:30:00',
            'purpose' => 'Sudah diisi',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $response = $this->actingAs($user)->get(route('reservations.create', [
            'facility_id' => $facility->id,
            'date' => $date,
            'slots' => '07:00',
        ]));

        $response->assertOk();
        // Pilihan user tetap terbawa (tidak ditimpa), namun ditandai bentrok
        $response->assertViewHas('startTime', '07:00');
        $response->assertViewHas('endTime', '07:30');
        $this->assertContains('07:00', $response->viewData('bookedSlots'));
    }
}
