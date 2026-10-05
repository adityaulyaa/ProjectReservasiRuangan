<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReportStatus;
use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Facility $facility;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
            'is_verified' => true,
        ]);

        $this->facility = Facility::create([
            'name' => 'Lab Komputer 1',
            'type' => 'Laboratorium',
            'location' => 'Gedung A Lt.2',
            'capacity' => 30,
            'description' => 'Laboratorium Komputer',
            'status' => FacilityStatus::ACTIVE->value,
        ]);
    }

    public function test_guest_cannot_access_create_report_page(): void
    {
        $response = $this->get(route('reports.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_create_report_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('reports.create'));

        $response->assertStatus(200);
        $response->assertSee('Laporkan Kerusakan Fasilitas');
        $response->assertSee('Lab Komputer 1');
    }

    public function test_user_can_submit_report_without_photo(): void
    {
        $response = $this->actingAs($this->user)->post(route('reports.store'), [
            'facility_id' => $this->facility->id,
            'category' => 'ac',
            'description' => 'AC tidak dingin dan mengeluarkan bunyi bising.',
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'user_id' => $this->user->id,
            'facility_id' => $this->facility->id,
            'category' => 'ac',
            'description' => 'AC tidak dingin dan mengeluarkan bunyi bising.',
            'photo_path' => null,
            'status' => ReportStatus::NEW->value,
        ]);

        $report = Report::where('user_id', $this->user->id)->first();
        $this->assertNotNull($report);

        $this->assertDatabaseHas('report_logs', [
            'report_id' => $report->id,
            'actor_id' => $this->user->id,
            'action' => 'created',
            'new_status' => ReportStatus::NEW->value,
        ]);
    }

    public function test_user_can_submit_report_with_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->create('damage.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->user)->post(route('reports.store'), [
            'facility_id' => $this->facility->id,
            'category' => 'listrik',
            'description' => 'Saklar berbunyi korslet dan mengeluarkan percikan.',
            'photo' => $photo,
        ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('success');

        $report = Report::where('user_id', $this->user->id)->first();
        $this->assertNotNull($report);
        $this->assertNotNull($report->photo_path);
        $this->assertStringStartsWith('reports/', $report->photo_path);

        Storage::disk('public')->assertExists($report->photo_path);
    }

    public function test_report_submission_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('reports.store'), [
            'facility_id' => 9999,
            'category' => 'invalid_category',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['facility_id', 'category', 'description']);
    }

    public function test_report_submission_fails_with_oversized_photo(): void
    {
        Storage::fake('public');

        // Photo oversized (> 2048 KB)
        $photo = UploadedFile::fake()->create('large_image.jpg', 3000);

        $response = $this->actingAs($this->user)->post(route('reports.store'), [
            'facility_id' => $this->facility->id,
            'category' => 'furniture',
            'description' => 'Meja patah di bagian kaki sebelah kiri.',
            'photo' => $photo,
        ]);

        $response->assertSessionHasErrors(['photo']);
    }

    public function test_user_can_view_their_report_index(): void
    {
        $otherUser = User::factory()->create(['role' => 'user', 'is_verified' => true]);

        $myReport = Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $this->facility->id,
            'category' => 'ac',
            'description' => 'Laporan milik saya.',
            'status' => ReportStatus::NEW->value,
        ]);

        $otherReport = Report::create([
            'user_id' => $otherUser->id,
            'facility_id' => $this->facility->id,
            'category' => 'it',
            'description' => 'Laporan milik user lain.',
            'status' => ReportStatus::NEW->value,
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee($this->facility->name);
        $response->assertViewHas('reports', function ($reports) use ($myReport, $otherReport) {
            return $reports->contains($myReport) && ! $reports->contains($otherReport);
        });
    }

    public function test_user_can_view_their_report_detail_with_logs(): void
    {
        $report = Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $this->facility->id,
            'category' => 'plumbing',
            'description' => 'Kran air bocor deras.',
            'status' => ReportStatus::NEW->value,
        ]);

        $report->logs()->create([
            'actor_id' => $this->user->id,
            'action' => 'created',
            'new_status' => 'new',
            'note' => 'Pengajuan laporan kerusakan awal',
        ]);

        $response = $this->actingAs($this->user)->get(route('reports.show', $report->id));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kerusakan #'.$report->id);
        $response->assertSee('Kran air bocor deras.');
        $response->assertSee('Pengajuan laporan kerusakan awal');
    }

    public function test_user_cannot_view_other_users_report_detail(): void
    {
        $otherUser = User::factory()->create(['role' => 'user', 'is_verified' => true]);

        $report = Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $this->facility->id,
            'category' => 'plumbing',
            'description' => 'Laporan pribadi user 1.',
            'status' => ReportStatus::NEW->value,
        ]);

        $response = $this->actingAs($otherUser)->get(route('reports.show', $report->id));

        $response->assertStatus(403);
    }

    public function test_staff_or_admin_can_view_any_report_detail(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_verified' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_verified' => true]);

        $report = Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $this->facility->id,
            'category' => 'listrik',
            'description' => 'Laporan user biasa.',
            'status' => ReportStatus::NEW->value,
        ]);

        // Staff can view
        $responseStaff = $this->actingAs($staff)->get(route('reports.show', $report->id));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('Laporan user biasa.');

        // Admin can view
        $responseAdmin = $this->actingAs($admin)->get(route('reports.show', $report->id));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Laporan user biasa.');
    }
}
