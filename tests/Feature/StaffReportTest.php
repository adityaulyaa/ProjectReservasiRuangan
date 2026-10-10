<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReportStatus;
use App\Enums\Role;
use App\Models\Facility;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffReportTest extends TestCase
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

    private function createReport(array $attrs = []): Report
    {
        return Report::create(array_merge([
            'user_id' => $this->createUser()->id,
            'facility_id' => $this->createFacility()->id,
            'category' => 'ac',
            'description' => 'AC tidak dingin dan mengeluarkan bunyi bising.',
            'status' => ReportStatus::NEW->value,
        ], $attrs));
    }

    // ─── Queue ───

    public function test_staff_can_view_report_queue(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport();

        $response = $this->actingAs($staff)->get(route('staff.reports.queue'));

        $response->assertStatus(200);
        $response->assertSee($report->facility->name);
        $response->assertSee($report->description);
    }

    public function test_non_staff_cannot_access_report_queue(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('staff.reports.queue'));

        $response->assertStatus(403);
    }

    public function test_queue_only_shows_new_and_in_progress_reports(): void
    {
        $staff = $this->createStaff();

        $new = $this->createReport(['status' => ReportStatus::NEW->value]);
        $inProgress = $this->createReport(['status' => ReportStatus::IN_PROGRESS->value]);
        $resolved = $this->createReport(['status' => ReportStatus::RESOLVED->value]);
        $rejected = $this->createReport(['status' => ReportStatus::REJECTED->value]);

        $response = $this->actingAs($staff)->get(route('staff.reports.queue'));

        $response->assertStatus(200);
        $response->assertViewHas('reports', function ($reports) use ($new, $inProgress, $resolved, $rejected) {
            return $reports->contains($new)
                && $reports->contains($inProgress)
                && ! $reports->contains($resolved)
                && ! $reports->contains($rejected);
        });
    }

    public function test_queue_can_filter_by_status(): void
    {
        $staff = $this->createStaff();
        $new = $this->createReport(['status' => ReportStatus::NEW->value]);
        $inProgress = $this->createReport(['status' => ReportStatus::IN_PROGRESS->value]);

        $response = $this->actingAs($staff)->get(route('staff.reports.queue', ['status' => 'new']));

        $response->assertStatus(200);
        $response->assertViewHas('reports', function ($reports) use ($new, $inProgress) {
            return $reports->contains($new) && ! $reports->contains($inProgress);
        });
    }

    // ─── Show ───

    public function test_staff_can_view_report_detail(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport();

        $response = $this->actingAs($staff)->get(route('staff.reports.show', $report->id));

        $response->assertStatus(200);
        $response->assertSee('Laporan Kerusakan #'.$report->id);
        $response->assertSee($report->description);
    }

    // ─── Update Status ───

    public function test_staff_can_mark_report_in_progress(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport(['status' => ReportStatus::NEW->value]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals(ReportStatus::IN_PROGRESS->value, $report->status->value);
        $this->assertEquals($staff->id, $report->processed_by);

        $this->assertDatabaseHas('report_logs', [
            'report_id' => $report->id,
            'action' => 'in_progress',
            'old_status' => ReportStatus::NEW->value,
            'new_status' => ReportStatus::IN_PROGRESS->value,
            'actor_id' => $staff->id,
        ]);
    }

    public function test_staff_can_resolve_report_with_note(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport(['status' => ReportStatus::IN_PROGRESS->value]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Kompresor AC telah diganti dan diisi freon baru.',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals(ReportStatus::RESOLVED->value, $report->status->value);
        $this->assertEquals('Kompresor AC telah diganti dan diisi freon baru.', $report->resolution_note);
        $this->assertEquals($staff->id, $report->processed_by);

        $this->assertDatabaseHas('report_logs', [
            'report_id' => $report->id,
            'action' => 'resolved',
            'new_status' => ReportStatus::RESOLVED->value,
            'actor_id' => $staff->id,
        ]);
    }

    public function test_staff_can_reject_report_with_note(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport(['status' => ReportStatus::IN_PROGRESS->value]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'rejected',
            'resolution_note' => 'Kerusakan di luar tanggung jawab pengelola fasilitas.',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));

        $report->refresh();
        $this->assertEquals(ReportStatus::REJECTED->value, $report->status->value);
        $this->assertEquals('Kerusakan di luar tanggung jawab pengelola fasilitas.', $report->resolution_note);

        $this->assertDatabaseHas('report_logs', [
            'report_id' => $report->id,
            'action' => 'rejected',
            'new_status' => ReportStatus::REJECTED->value,
            'actor_id' => $staff->id,
        ]);
    }

    public function test_resolving_requires_note(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport(['status' => ReportStatus::IN_PROGRESS->value]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => '',
        ]);

        $response->assertSessionHasErrors('resolution_note');

        $report->refresh();
        $this->assertEquals(ReportStatus::IN_PROGRESS->value, $report->status->value);
    }

    public function test_update_status_fails_with_invalid_transition(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport(['status' => ReportStatus::RESOLVED->value]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'in_progress',
        ]);

        $response->assertSessionHasErrors('status');

        $report->refresh();
        $this->assertEquals(ReportStatus::RESOLVED->value, $report->status->value);
    }

    public function test_update_status_fails_with_invalid_status_value(): void
    {
        $staff = $this->createStaff();
        $report = $this->createReport();

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'new',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Facility Maintenance/Active ───

    public function test_staff_can_mark_facility_maintenance(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::ACTIVE->value]);
        $report = $this->createReport(['facility_id' => $facility->id]);

        $response = $this->actingAs($staff)->post(route('staff.reports.markMaintenance', $report->id));

        $response->assertRedirect(route('staff.reports.queue'));
        $response->assertSessionHas('success');

        $facility->refresh();
        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->status->value);
    }

    public function test_staff_can_mark_facility_active(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::RESOLVED->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reports.markActive', $report->id));

        $response->assertRedirect(route('staff.reports.queue'));
        $response->assertSessionHas('success');

        $facility->refresh();
        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->status->value);
    }

    public function test_resolving_report_automatically_activates_facility_when_no_other_under_repair(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Masalah telah diperbaiki',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        
        $facility->refresh();
        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->status->value);
    }

    public function test_resolving_report_keeps_facility_maintenance_when_other_under_repair_exists(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        
        // Report pertama - akan diselesaikan
        $report1 = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::UNDER_REPAIR->value,
            'category' => 'ac',
        ]);

        // Report kedua - masih under_repair
        $report2 = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::UNDER_REPAIR->value,
            'category' => 'listrik',
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report1->id), [
            'status' => 'resolved',
            'resolution_note' => 'AC telah diperbaiki',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        
        // Fasilitas harus tetap maintenance karena masih ada report2 yang under_repair
        $facility->refresh();
        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->status->value);
    }

    public function test_rejecting_report_automatically_activates_facility_when_no_other_under_repair(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'rejected',
            'resolution_note' => 'Laporan tidak valid, tidak ditemukan kerusakan',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        
        $facility->refresh();
        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->status->value);
    }

    public function test_in_progress_status_does_not_change_facility_status(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::ACTIVE->value]);
        $report = $this->createReport([
            'facility_id' => $facility->id,
            'status' => ReportStatus::NEW->value,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('staff.reports.queue'));
        
        // Fasilitas harus tetap active
        $facility->refresh();
        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->status->value);
    }
}
