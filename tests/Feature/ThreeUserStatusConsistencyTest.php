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

class ThreeUserStatusConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $name, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => Role::USER->value,
            'is_verified' => true,
        ]);
    }

    public function test_all_users_see_same_status_after_report_resolved(): void
    {
        $staff = User::factory()->create([
            'name' => 'Staff Satu',
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $ahmad = $this->user('Ahmad Fauzi', 'ahmad.fauzi@student.ac.id');
        $other = $this->user('Siti Nurhaliza', 'siti.nurhaliza@student.ac.id');

        $facility = Facility::create([
            'name' => 'Ruang Uji Konsistensi',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Z',
            'capacity' => 10,
            'description' => 'Uji konsistensi.',
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

        $report = Report::create([
            'user_id' => $ahmad->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'AC rusak.',
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        // Staff resolves the only active report
        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $report->id), [
            'status' => 'resolved',
            'resolution_note' => 'Sudah diperbaiki.',
        ])->assertRedirect(route('staff.reports.queue'));

        $facility->refresh();
        $this->assertEquals(FacilityStatus::ACTIVE->value, $facility->status->value, 'Facility should be active after resolve');

        // All three accounts must see the same status on list + availability
        foreach ([$staff, $other, $ahmad] as $account) {
            $list = $this->actingAs($account)->get(route('facilities.index'));
            $list->assertOk();
            $availability = $this->actingAs($account)->get(route('facilities.availability', $facility->id));
            $availability->assertOk();
            $availability->assertDontSee('Fasilitas Sedang Dalam Perbaikan');
            $list->assertSee('Ruang Uji Konsistensi');
        }
    }

    public function test_all_users_see_maintenance_when_other_active_report_remains(): void
    {
        $staff = User::factory()->create([
            'name' => 'Staff Satu',
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $ahmad = $this->user('Ahmad Fauzi', 'ahmad.fauzi@student.ac.id');
        $other = $this->user('Siti Nurhaliza', 'siti.nurhaliza@student.ac.id');

        $facility = Facility::create([
            'name' => 'Ruang Uji Ganda',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Z',
            'capacity' => 10,
            'description' => 'Uji konsistensi ganda.',
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

        // Ahmad's report resolved, but Siti's report still active
        $ahmadReport = Report::create([
            'user_id' => $ahmad->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'AC rusak.',
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);
        Report::create([
            'user_id' => $other->id,
            'facility_id' => $facility->id,
            'category' => 'listrik',
            'description' => 'Lampu mati.',
            'status' => ReportStatus::NEW->value,
        ]);

        $this->actingAs($staff)->post(route('staff.reports.updateStatus', $ahmadReport->id), [
            'status' => 'resolved',
            'resolution_note' => 'AC sudah diperbaiki.',
        ]);

        $facility->refresh();
        $this->assertEquals(FacilityStatus::MAINTENANCE->value, $facility->status->value);

        // All users should consistently see maintenance (still an active report)
        foreach ([$staff, $other, $ahmad] as $account) {
            $availability = $this->actingAs($account)->get(route('facilities.availability', $facility->id));
            $availability->assertOk();
            $availability->assertSee('Fasilitas Sedang Dalam Perbaikan');
        }
    }

    public function test_under_repair_report_badge_is_consistent_between_owner_and_staff(): void
    {
        $staff = User::factory()->create([
            'name' => 'Staff Satu',
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $ahmad = $this->user('Ahmad Fauzi', 'ahmad.fauzi@student.ac.id');

        $facility = Facility::create([
            'name' => 'Ruang Uji Badge',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Z',
            'capacity' => 10,
            'description' => 'Uji badge.',
            'status' => FacilityStatus::MAINTENANCE->value,
        ]);

        $report = Report::create([
            'user_id' => $ahmad->id,
            'facility_id' => $facility->id,
            'category' => 'ac',
            'description' => 'AC rusak.',
            'status' => ReportStatus::UNDER_REPAIR->value,
        ]);

        // Owner's own report list + detail must render the friendly label
        $this->actingAs($ahmad)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Sedang Diperbaiki');
        $this->actingAs($ahmad)->get(route('reports.show', $report->id))
            ->assertOk()
            ->assertSee('Sedang Diperbaiki');

        // Staff detail view must render the same label
        $this->actingAs($staff)->get(route('staff.reports.show', $report->id))
            ->assertOk()
            ->assertSee('Sedang Diperbaiki');
    }
}
