<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_and_availability_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/facilities')->assertOk()->assertSee('Lab Komputer RPL');
        $this->get('/facilities/1/availability')->assertOk();
    }

    public function test_demo_accounts_can_access_their_dashboards(): void
    {
        $admin = User::where('email', 'admin@kampus.ac.id')->firstOrFail();
        $staff = User::where('email', 'staff1@kampus.ac.id')->firstOrFail();
        $user = User::where('email', 'ahmad.fauzi@student.ac.id')->firstOrFail();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Dashboard Administrator');
        $this->actingAs($staff)->get('/staff/dashboard')->assertOk()->assertSee('Dashboard Petugas');
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Dashboard Pengguna');
    }

    public function test_role_access_control_and_unverified_login_block(): void
    {
        $admin = User::where('email', 'admin@kampus.ac.id')->firstOrFail();
        $staff = User::where('email', 'staff1@kampus.ac.id')->firstOrFail();
        $user = User::where('email', 'ahmad.fauzi@student.ac.id')->firstOrFail();

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($staff)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/staff/dashboard')->assertForbidden();
        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => 'rina.wati@student.ac.id',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_export_csv_excel_and_pdf_have_expected_formats(): void
    {
        $admin = User::where('email', 'admin@kampus.ac.id')->firstOrFail();

        $csv = $this->actingAs($admin)->get(route('admin.reports.export.csv'));
        $csv->assertOk();
        $this->assertStringContainsString('text/csv', $csv->headers->get('Content-Type'));

        $excel = $this->actingAs($admin)->get(route('admin.reports.export.excel'));
        $excel->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $excel->headers->get('Content-Type'));
        $this->assertStringContainsString('<table', $excel->getContent());

        $pdf = $this->actingAs($admin)->get(route('admin.reports.export.pdf'));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }
}
