<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\Role;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFacilityTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);
    }

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
            'name' => 'Ruang Teori 101',
            'type' => 'Ruang Kelas',
            'location' => 'Gedung A Lt.1',
            'capacity' => 40,
            'description' => 'Ruang kelas ber-AC dengan proyektor.',
            'status' => FacilityStatus::ACTIVE->value,
            'image_url' => 'https://example.com/facility.jpg',
        ], $attrs));
    }

    // ─── Akses & Autentikasi ───

    public function test_guest_cannot_access_admin_facilities(): void
    {
        $response = $this->get('/admin/facilities');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_facilities(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/admin/facilities');
        $response->assertStatus(403);
    }

    public function test_staff_cannot_access_admin_facilities(): void
    {
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get('/admin/facilities');
        $response->assertStatus(403);
    }

    // ─── Index & Pencarian ───

    public function test_admin_can_view_facilities_index(): void
    {
        $admin = $this->createAdmin();
        $this->createFacility(['name' => 'Lab Fisika']);
        $this->createFacility(['name' => 'Lab Kimia']);

        $response = $this->actingAs($admin)->get('/admin/facilities');

        $response->assertStatus(200);
        $response->assertViewIs('admin.facilities.index');
        $response->assertViewHas('facilities');
        $response->assertSee('Lab Fisika');
        $response->assertSee('Lab Kimia');
    }

    public function test_admin_can_search_and_filter_facilities(): void
    {
        $admin = $this->createAdmin();
        $this->createFacility(['name' => 'Auditorium Barat', 'status' => 'active']);
        $this->createFacility(['name' => 'Ruang Server Khusus', 'status' => 'inactive']);

        $searchResponse = $this->actingAs($admin)->get('/admin/facilities?search=Auditorium');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Auditorium Barat');
        $searchResponse->assertDontSee('Ruang Server Khusus');

        $filterResponse = $this->actingAs($admin)->get('/admin/facilities?status=inactive');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Ruang Server Khusus');
        $filterResponse->assertDontSee('Auditorium Barat');
    }

    // ─── Create & Store ───

    public function test_admin_can_view_create_facility_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/facilities/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.facilities.create');
    }

    public function test_admin_can_store_new_facility(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Lab Robotika Modern',
            'type' => 'Laboratorium',
            'location' => 'Gedung C Lt.3',
            'capacity' => 25,
            'description' => 'Dilengkapi peralatan mikrokontroler dan lengan robotik.',
            'image_url' => 'https://example.com/robotika.jpg',
            'status' => 'active',
        ];

        $response = $this->actingAs($admin)->post('/admin/facilities', $payload);

        $response->assertRedirect('/admin/facilities');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'name' => 'Lab Robotika Modern',
            'type' => 'Laboratorium',
            'location' => 'Gedung C Lt.3',
            'capacity' => 25,
            'status' => 'active',
        ]);
    }

    public function test_store_facility_validates_required_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/facilities', [
            'name' => '',
            'type' => '',
            'location' => '',
            'capacity' => -5,
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['name', 'type', 'location', 'capacity', 'status']);
    }

    // ─── Show & Edit & Update ───

    public function test_admin_can_view_facility_show_page(): void
    {
        $admin = $this->createAdmin();
        $facility = $this->createFacility(['name' => 'Aula Serbaguna']);

        $response = $this->actingAs($admin)->get("/admin/facilities/{$facility->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.facilities.show');
        $response->assertSee('Aula Serbaguna');
    }

    public function test_admin_can_view_facility_edit_page(): void
    {
        $admin = $this->createAdmin();
        $facility = $this->createFacility(['name' => 'Lab Komputer 1']);

        $response = $this->actingAs($admin)->get("/admin/facilities/{$facility->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.facilities.edit');
        $response->assertSee('Lab Komputer 1');
    }

    public function test_admin_can_update_facility(): void
    {
        $admin = $this->createAdmin();
        $facility = $this->createFacility([
            'name' => 'Ruang Diskusi A',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put("/admin/facilities/{$facility->id}", [
            'name' => 'Ruang Diskusi A - Terbarukan',
            'type' => 'Study Room',
            'location' => 'Gedung B Lt.2',
            'capacity' => 15,
            'description' => 'Kapasitas ditingkatkan.',
            'image_url' => 'https://example.com/diskusi.jpg',
            'status' => 'maintenance',
        ]);

        $response->assertRedirect('/admin/facilities');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Ruang Diskusi A - Terbarukan',
            'capacity' => 15,
            'status' => 'maintenance',
        ]);
    }

    // ─── Status Toggle & Nonaktifkan ───

    public function test_admin_can_toggle_facility_status(): void
    {
        $admin = $this->createAdmin();
        $facility = $this->createFacility(['status' => 'active']);

        // Toggle dari active -> inactive
        $response = $this->actingAs($admin)->patch("/admin/facilities/{$facility->id}/toggle-status");
        $response->assertRedirect();
        $this->assertEquals(FacilityStatus::INACTIVE, $facility->fresh()->status);

        // Toggle dari inactive -> active
        $response2 = $this->actingAs($admin)->patch("/admin/facilities/{$facility->id}/toggle-status");
        $response2->assertRedirect();
        $this->assertEquals(FacilityStatus::ACTIVE, $facility->fresh()->status);
    }

    public function test_inactive_facility_is_not_displayed_on_public_page(): void
    {
        $activeFacility = $this->createFacility(['name' => 'Fasilitas Terbuka', 'status' => 'active']);
        $inactiveFacility = $this->createFacility(['name' => 'Fasilitas Rahasia', 'status' => 'inactive']);

        $response = $this->get('/facilities');

        $response->assertStatus(200);
        $response->assertSee($activeFacility->name);
        $response->assertDontSee($inactiveFacility->name);
    }

    // ─── Hapus & Proteksi Relasi ───

    public function test_admin_can_delete_facility_without_relations(): void
    {
        $admin = $this->createAdmin();
        $facility = $this->createFacility(['name' => 'Ruang Sementara']);

        $response = $this->actingAs($admin)->delete("/admin/facilities/{$facility->id}");

        $response->assertRedirect('/admin/facilities');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
    }

    public function test_admin_cannot_delete_facility_with_reservations(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $facility = $this->createFacility(['name' => 'Ruang Bersejarah']);

        // Buat reservasi terkait
        Reservation::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'reservation_date' => now()->addDay()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Seminar Teknologi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/facilities/{$facility->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('facilities', ['id' => $facility->id]);
    }

    public function test_admin_cannot_delete_facility_with_reports(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $facility = $this->createFacility(['name' => 'Ruang Rusak']);

        // Buat laporan terkait
        Report::create([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'AC bocor dan korsleting.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/facilities/{$facility->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('facilities', ['id' => $facility->id]);
    }
}
