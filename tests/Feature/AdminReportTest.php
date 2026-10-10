<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Enums\ReportStatus;
use App\Enums\ReservationStatus;
use App\Enums\Role;
use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::ADMIN->value,
            'is_verified' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => Role::STAFF->value,
            'is_verified' => true,
        ]);

        $this->user = User::factory()->create([
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
            'description' => 'Ruang teori ber-AC',
            'status' => FacilityStatus::ACTIVE->value,
        ], $attrs));
    }

    // ─── Akses & Autentikasi ───

    public function test_guest_cannot_access_admin_reports(): void
    {
        $response = $this->get(route('admin.reports.index'));
        $response->assertRedirect('/login');

        $responseCsv = $this->get(route('admin.reports.export.csv'));
        $responseCsv->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_reports(): void
    {
        $responseUser = $this->actingAs($this->user)->get(route('admin.reports.index'));
        $responseUser->assertForbidden();

        $responseStaff = $this->actingAs($this->staff)->get(route('admin.reports.index'));
        $responseStaff->assertForbidden();
    }

    // ─── Halaman Rekap & Filter ───

    public function test_admin_can_view_reports_dashboard_with_stats(): void
    {
        $facilityA = $this->createFacility(['name' => 'Auditorium Utama', 'location' => 'Gedung Pusat']);
        $facilityB = $this->createFacility(['name' => 'Lab Fisika', 'location' => 'Gedung B']);

        // Reservasi approved untuk facilityA (3 jam)
        Reservation::create([
            'user_id' => $this->user->id,
            'facility_id' => $facilityA->id,
            'reservation_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Seminar Ilmiah',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        // Reservasi pending (tidak boleh dihitung sebagai approved)
        Reservation::create([
            'user_id' => $this->user->id,
            'facility_id' => $facilityA->id,
            'reservation_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Latihan',
            'status' => ReservationStatus::PENDING->value,
        ]);

        // Laporan kerusakan untuk facilityA
        Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $facilityA->id,
            'category' => 'Elektronik',
            'description' => 'AC tidak dingin dan berbunyi',
            'status' => ReportStatus::NEW->value,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Okupansi');
        $response->assertSee('Auditorium Utama');
        $response->assertSee('Lab Fisika');
        $response->assertSee('Export CSV');
        $response->assertSee('Export Excel');
        $response->assertSee('Export PDF');

        // Stats assertions
        $stats = $response->viewData('stats');
        $this->assertEquals(2, $stats['total_facilities']);
        $this->assertEquals(1, $stats['total_approved_reservations']);
        $this->assertEquals(1, $stats['total_reports']);
    }

    public function test_reports_filter_by_facility_and_location(): void
    {
        $facilityA = $this->createFacility(['name' => 'Ruang Seminar VIP', 'location' => 'Gedung Rektorat']);
        $facilityB = $this->createFacility(['name' => 'Lab Bahasa', 'location' => 'Gedung Bahasa Lt.2']);

        // Filter by facility_id
        $responseFacility = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'facility_id' => $facilityA->id,
        ]));
        $responseFacility->assertStatus(200);
        $facilityNames = $responseFacility->viewData('recapData')->pluck('name')->toArray();
        $this->assertContains('Ruang Seminar VIP', $facilityNames);
        $this->assertNotContains('Lab Bahasa', $facilityNames);

        // Filter by location
        $responseLocation = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'location' => 'Gedung Bahasa Lt.2',
        ]));
        $responseLocation->assertStatus(200);
        $locationNames = $responseLocation->viewData('recapData')->pluck('name')->toArray();
        $this->assertContains('Lab Bahasa', $locationNames);
        $this->assertNotContains('Ruang Seminar VIP', $locationNames);
    }

    public function test_reports_filter_by_date_range(): void
    {
        $facility = $this->createFacility(['name' => 'Studio Musik']);

        // Reservasi di luar rentang (2 bulan lalu)
        Reservation::create([
            'user_id' => $this->user->id,
            'facility_id' => $facility->id,
            'reservation_date' => Carbon::now()->subMonths(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Latihan Band Lama',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        // Reservasi di dalam rentang (hari ini)
        Reservation::create([
            'user_id' => $this->user->id,
            'facility_id' => $facility->id,
            'reservation_date' => Carbon::today()->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'purpose' => 'Latihan Band Baru',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        $from = Carbon::today()->subDays(2)->format('Y-m-d');
        $to = Carbon::today()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'from' => $from,
            'to' => $to,
        ]));

        $response->assertStatus(200);
        $recap = $response->viewData('recapData')->firstWhere('name', 'Studio Musik');
        $this->assertNotNull($recap);
        $this->assertEquals(1, $recap['approved_reservations_count']);
        $this->assertEquals(2.0, $recap['approved_hours']);
    }

    // ─── Export Features ───

    public function test_admin_can_export_csv(): void
    {
        $facility = $this->createFacility([
            'name' => 'Lab Jaringan Komputer',
            'type' => 'Laboratorium',
            'location' => 'Gedung D',
        ]);

        Reservation::create([
            'user_id' => $this->user->id,
            'facility_id' => $facility->id,
            'reservation_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Praktikum Cisco',
            'status' => ReservationStatus::APPROVED->value,
        ]);

        Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $facility->id,
            'category' => 'Jaringan',
            'description' => 'Kabel LAN port 3 putus',
            'status' => ReportStatus::NEW->value,
        ]);

        // Via direct route
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('rekap-', $disposition);
        $this->assertStringContainsString('.csv', $disposition);

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Verifikasi header CSV & data baris
        $this->assertStringContainsString('Tipe Fasilitas', $content);
        $this->assertStringContainsString('Nama Fasilitas', $content);
        $this->assertStringContainsString('Okupansi (%)', $content);
        $this->assertStringContainsString('Jumlah Laporan Kerusakan', $content);
        $this->assertStringContainsString('Lab Jaringan Komputer', $content);
        $this->assertStringContainsString('Gedung D', $content);
    }

    public function test_admin_can_export_excel(): void
    {
        $facility = $this->createFacility([
            'name' => 'Ruang Sidang Utama',
            'type' => 'Ruang Rapat',
            'location' => 'Gedung Rektorat Lt.3',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.excel'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('rekap-', $disposition);
        $this->assertStringContainsString('.xls', $disposition);

        $content = $response->getContent();
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('Ruang Sidang Utama', $content);
        $this->assertStringContainsString('Gedung Rektorat Lt.3', $content);
    }

    public function test_admin_can_export_pdf(): void
    {
        $this->createFacility([
            'name' => 'Aula Serbaguna',
            'type' => 'Aula',
            'location' => 'Gedung Olahraga',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.export.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('rekap-', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);

        $content = $response->getContent();
        // PDF files start with %PDF-
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_admin_reports_universal_export_endpoint(): void
    {
        $this->createFacility(['name' => 'Ruang Musik']);

        // Format CSV
        $responseCsv = $this->actingAs($this->admin)->get(route('admin.reports.export', ['format' => 'csv']));
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseCsv->headers->get('Content-Type'));

        // Format Excel
        $responseExcel = $this->actingAs($this->admin)->get(route('admin.reports.export', ['format' => 'excel']));
        $responseExcel->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $responseExcel->headers->get('Content-Type'));

        // Format PDF
        $responsePdf = $this->actingAs($this->admin)->get(route('admin.reports.export', ['format' => 'pdf']));
        $responsePdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
    }

    public function test_admin_report_show_redirects_to_public_report_show(): void
    {
        $report = Report::create([
            'user_id' => $this->user->id,
            'facility_id' => $this->createFacility()->id,
            'category' => 'Peralatan',
            'description' => 'Lampu padam',
            'status' => ReportStatus::NEW->value,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.show', $report->id));
        $response->assertRedirect(route('reports.show', $report->id));
    }
}
