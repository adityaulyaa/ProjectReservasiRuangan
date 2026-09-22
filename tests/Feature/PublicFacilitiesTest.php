<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Enums\FacilityStatus;
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
        $this->assertCount(9, $facilities);
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

    private function createFacility(array $attributes = []): Facility
    {
        $defaults = [
            'name' => 'Test Facility ' . uniqid(),
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
                'name' => 'Facility ' . ($i + 1),
            ], $attributes));
        }
    }
}
