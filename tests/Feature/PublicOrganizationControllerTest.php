<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\OrganizationController;
use App\Http\Resources\Public\OrganizationResource;
use App\Models\Organization;
use App\Repositories\Organization\OrganizationRepositoryInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Tests\TestCase;

class PublicOrganizationControllerTest extends TestCase
{
    public function testFindOrganizationById(): void
    {
        /**
         * @var Organization $organization
         */
        $organization = new class extends Organization {
            public function reportCount(): Attribute
            {
                return Attribute::make(get: fn () => $this->reports->count());
            }
        };
        $organization->forceFill([
            'id' => 1,
            'name' => 'Ecohuertas',
            'country' => 'MX',
            'avatar_path' => null,
            'is_active' => true,
            'created_at' => '2026-08-19 12:00:00',
        ]);
        $organization->setRelation('reports', collect());

        $this->bindOrganizationRepository($organization);

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
        $calendar = collect(range(1, 12))
            ->mapWithKeys(fn (int $month) => [$month => $month === 1 ? 3 : 0]);

        $repository = $this->createMock(OrganizationRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findCalendarByOrganizationIdAndYear')
            ->with(1, 2026)
            ->willReturn($calendar);

        $this->app->when(OrganizationController::class)
            ->needs(OrganizationRepositoryInterface::class)
            ->give(fn () => $repository);

        $response = $this->getJson('/api/public/organizations/1/calendar/2026');

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

    private function bindOrganizationRepository(Organization $organization): void
    {
        $repository = $this->createMock(OrganizationRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOrganizationById')
            ->with($organization->id)
            ->willReturn($organization);

        $this->app->when(OrganizationController::class)
            ->needs(OrganizationRepositoryInterface::class)
            ->give(fn () => $repository);
    }
}
