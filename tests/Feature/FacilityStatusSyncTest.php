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

class FacilityStatusSyncTest extends TestCase
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
            'name' => 'Ruang Uji Sinkronisasi',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Z Lt.1',
            'capacity' => 20,
            'description' => 'Fasilitas untuk pengujian sinkronisasi status.',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attrs));
    }

    private function createReport(User $user, Facility $facility, array $attrs = []): Report
    {
        return Report::create(array_merge([
            'user_id' => $user->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'Kerusakan untuk pengujian sinkronisasi.',
            'status' => ReportStatus::NEW->value,
        ], $attrs));
    }

    public function test_resolving_last_active_report_activates_facility(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($this->createUser(), $facility, [
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan selesai.',
        ])->assertRedirect(route('staff.reports.queue'));

        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->refresh()->status->value);
    }

    public function test_resolving_keeps_maintenance_when_other_new_report_exists(): void
    {
        $staff = $this->createStaff();
        $user = $this->createUser();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($user, $facility, ['status' => ReportStatus::UNDER_REPAIR->value]);
        // Laporan aktif lain yang belum diproses
        $this->createReport($user, $facility, ['status' => ReportStatus::NEW->value]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan pertama selesai.',
        ]);

        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->refresh()->status->value);
    }

    public function test_resolving_keeps_maintenance_when_other_in_progress_report_exists(): void
    {
        $staff = $this->createStaff();
        $user = $this->createUser();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($user, $facility, ['status' => ReportStatus::UNDER_REPAIR->value]);
        $this->createReport($user, $facility, ['status' => ReportStatus::IN_PROGRESS->value]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan pertama selesai.',
        ]);

        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->refresh()->status->value);
    }

    public function test_resolving_does_not_activate_inactive_facility(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::INACTIVE->value]);
        $report = $this->createReport($this->createUser(), $facility, [
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan selesai.',
        ]);

        $this->assertEquals(FacilityStatus::INACTIVE->value, $facility->refresh()->status->value);
    }

    public function test_availability_page_reflects_facility_status_after_resolve(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($this->createUser(), $facility, [
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        // Sebelum: kalender menampilkan status perbaikan
        $this->get(route('facilities.availability', $facility->id))
            ->assertOk()
            ->assertSee('Fasilitas Sedang Dalam Perbaikan');

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan selesai.',
        ]);

        // Sesudah: kalender menampilkan status tersedia
        $this->get(route('facilities.availability', $facility->id))
            ->assertOk()
            ->assertSee('Tersedia')
            ->assertDontSee('Fasilitas Sedang Dalam Perbaikan');
    }

    public function test_facility_list_reflects_updated_status_after_resolve(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility([
            'name' => 'Ruang Sinkronisasi Unik',
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);
        $report = $this->createReport($this->createUser(), $facility, [
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan selesai.',
        ]);

        $response = $this->get(route('facilities.index'))->assertOk();
        $response->assertSee('Ruang Sinkronisasi Unik');
        $response->assertSee('Aktif');
    }

    public function test_manual_activation_does_not_override_active_reports(): void
    {
        $staff = $this->createStaff();
        $user = $this->createUser();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($user, $facility, ['status' => ReportStatus::UNDER_REPAIR->value]);
        // Laporan aktif lain yang masih berjalan
        $this->createReport($user, $facility, ['status' => ReportStatus::IN_PROGRESS->value]);

        $this->actingAs($staff)->post(route('staff.reports.markActive', $report->id));

        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->refresh()->status->value);
    }

    public function test_staff_dashboard_renders_after_resolve(): void
    {
        $staff = $this->createStaff();
        $facility = $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $report = $this->createReport($this->createUser(), $facility, [
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Perbaikan selesai.',
        ]);

        $this->actingAs($staff)->get(route('staff.dashboard'))->assertOk();
        $this->actingAs($staff)->get(route('staff.reports.queue'))->assertOk();
    }
}
