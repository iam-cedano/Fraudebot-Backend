<?php

namespace Tests\Feature;

use App\Http\Resources\Public\OrganizationResource;
use App\Models\Organization;
use App\Models\Report;
use Tests\TestCase;

class PublicOrganizationControllerTest extends TestCase
{
    public function testFindOrganizationById(): void
    {
        /**
         * @var Organization $organization
         */
        $organization = Organization::factory()->create();

        $response = $this->getJson("/api/public/organizations/{$organization->id}");

        $response->assertStatus(200);
        $response->assertExactJson(OrganizationResource::make($organization)->resolve());
    }

    public function testFindOrganizationByInvalidIdReturns400(): void
    {
        $response = $this->getJson("/api/public/organizations/9999999999999999999999999999999999999999");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid organization ID']);
    }

    public function testFindOrganizationByNonExistentIdReturns404(): void
    {
        $response = $this->getJson("/api/public/organizations/0");

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Organization not found']);
    }

    public function testFindOrganizationCalendarByIdAndYear(): void
    {
        /**
         * @var Organization $organization
         */
        $organization = Organization::factory()->create();

        $reports = Report::factory()->count(3)->create([
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ]);

        $organization->reports()->attach($reports->pluck('id'));

        $calendar = collect(range(1, 12))
            ->mapWithKeys(fn(int $month) => [$month => $month === 1 ? 3 : 0]);

        $response = $this->getJson("/api/public/organizations/{$organization->id}/calendar/2026");

        $response->assertStatus(200);
        $response->assertExactJson($calendar->toArray());
    }

    public function testFindOrganizationCalendarByInvalidIdReturns400(): void
    {
        $response = $this->getJson('/api/public/organizations/9999999999999999999999999999999999999999/calendar/2026');

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid organization ID or year']);
    }

    public function testFindOrganizationCalendarByInvalidYearReturns400(): void
    {
        $response = $this->getJson('/api/public/organizations/1/calendar/not-a-year');

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid organization ID or year']);
    }

    public function testFindOrganizationCalendarByNonExistentIdReturns404(): void
    {
        $response = $this->getJson('/api/public/organizations/0/calendar/2026');

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Organization not found']);
    }
}
