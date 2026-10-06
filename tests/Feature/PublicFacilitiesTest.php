<?php

namespace Tests\Feature;

use App\Enums\FacilityStatus;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicFacilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_can_be_rendered(): void
    {
        $this->createFacilities(3, ['status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('public.index');
        $response->assertViewHas('facilities');
    }

    public function test_facilities_page_can_be_rendered(): void
    {
        $this->createFacilities(15, ['status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities');

        $response->assertStatus(200);
        $response->assertViewIs('public.facilities');
        $response->assertViewHas('facilities');
    }

    public function test_facilities_page_paginates_9_per_page(): void
    {
        $this->createFacilities(20, ['status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities');

        $facilities = $response->viewData('facilities');
        $this->assertCount(20, $facilities);
    }

    public function test_only_active_and_maintenance_facilities_shown(): void
    {
        $this->createFacility(['status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['status' => FacilityStatus::MAINTENANCE->value]);
        $this->createFacility(['status' => FacilityStatus::INACTIVE->value]);

        $response = $this->get('/facilities');

        $facilities = $response->viewData('facilities');
        $this->assertCount(2, $facilities);
    }

    public function test_facilities_are_ordered_by_name(): void
    {
        $this->createFacility([
            'name' => 'Zebra Hall',
            'status' => FacilityStatus::ACTIVE->value,
        ]);
        $this->createFacility([
            'name' => 'Apple Room',
            'status' => FacilityStatus::ACTIVE->value,
        ]);

        $response = $this->get('/facilities');

        $facilities = $response->viewData('facilities');
        $this->assertEquals('Apple Room', $facilities[0]->name);
        $this->assertEquals('Zebra Hall', $facilities[1]->name);
    }

    public function test_home_page_takes_6_facilities(): void
    {
        $this->createFacilities(10, ['status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/');

        $facilities = $response->viewData('facilities');
        $this->assertCount(6, $facilities);
    }

    public function test_facility_card_component_displays_correctly(): void
    {
        $this->createFacility([
            'name' => 'Test Room',
            'capacity' => 50,
            'status' => FacilityStatus::ACTIVE->value,
        ]);

        $this->createFacility([
            'name' => 'Test Room',
            'capacity' => 50,
            'status' => FacilityStatus::ACTIVE->value,
        ]);

        $response = $this->get('/facilities');

        $response->assertSee('Test Room');
        $response->assertSee('50 orang');
    }

    public function test_can_filter_facilities_by_search_name(): void
    {
        $this->createFacility(['name' => 'Lab Komputer A', 'status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['name' => 'Ruang Seminar', 'status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities?search=Komputer');

        $response->assertStatus(200);
        $facilities = $response->viewData('facilities');
        $this->assertCount(1, $facilities);
        $this->assertEquals('Lab Komputer A', $facilities->first()->name);
    }

    public function test_can_filter_facilities_by_type(): void
    {
        $this->createFacility(['name' => 'Ruang 1', 'type' => 'Laboratorium', 'status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['name' => 'Ruang 2', 'type' => 'Kelas', 'status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities?type=Laboratorium');

        $response->assertStatus(200);
        $facilities = $response->viewData('facilities');
        $this->assertCount(1, $facilities);
        $this->assertEquals('Ruang 1', $facilities->first()->name);
    }

    public function test_can_filter_facilities_by_location(): void
    {
        $this->createFacility(['name' => 'Ruang 1', 'location' => 'Gedung A Lt.2', 'status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['name' => 'Ruang 2', 'location' => 'Gedung B Lt.1', 'status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities?location=Gedung+A');

        $response->assertStatus(200);
        $facilities = $response->viewData('facilities');
        $this->assertCount(1, $facilities);
        $this->assertEquals('Ruang 1', $facilities->first()->name);
    }

    public function test_can_filter_facilities_by_capacity_min(): void
    {
        $this->createFacility(['name' => 'Kecil', 'capacity' => 10, 'status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['name' => 'Sedang', 'capacity' => 30, 'status' => FacilityStatus::ACTIVE->value]);
        $this->createFacility(['name' => 'Besar', 'capacity' => 100, 'status' => FacilityStatus::ACTIVE->value]);

        $response = $this->get('/facilities?capacity_min=30');

        $response->assertStatus(200);
        $facilities = $response->viewData('facilities');
        $this->assertCount(2, $facilities);
        $this->assertFalse($facilities->contains('name', 'Kecil'));
        $this->assertTrue($facilities->contains('name', 'Sedang'));
        $this->assertTrue($facilities->contains('name', 'Besar'));
    }

    private function createFacility(array $attributes = []): Facility
    {
        $defaults = [
            'name' => 'Test Facility '.uniqid(),
            'type' => 'Ruang Rapat',
            'location' => 'Gedung A',
            'capacity' => 10,
            'description' => 'Test facility description',
            'status' => FacilityStatus::ACTIVE->value,
        ];

        return Facility::create(array_merge($defaults, $attributes));
    }

    private function createFacilities(int $count, array $attributes = []): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->createFacility(array_merge([
                'name' => 'Facility '.($i + 1),
            ], $attributes));
        }
    }
}
