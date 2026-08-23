<?php

namespace Tests\Feature;

use App\Domain\Contact\Enums\PlatformType;
use App\Http\Resources\Public\ContactResource;
use App\Http\Resources\Public\OrganizationResource;
use App\Models\Contact;
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

    public function testFindOrganizationContactsById(): void
    {
        $organization = Organization::factory()->create();

        $contacts = Contact::factory()->count(3)->create();

        $organization->contacts()->attach($contacts->pluck('id'));

        $page = 1;
        $count = 10;
        $expected = [
            'data' => ContactResource::collection($contacts)->resolve(),
            'total' => $contacts->count(),
            'page' => $page,
            'count' => $count,
        ];

        $response = $this->getJson("/api/public/organizations/{$organization->id}/contacts?p={$page}&c={$count}");

        $response->assertStatus(200);
        $response->assertExactJson($expected);
    }

    public function testFindoOrganizationContactsByIdWithPlatformQueryParam(): void
    {
        $organization = Organization::factory()->create();
        $contacts = Contact::factory()->createMany([
            [
                'name' => 'John Doe',
                'reference' => 'john-doe',
                'platform' => PlatformType::INSTAGRAM,
                'is_active' => true,
            ],
            [
                'name' => 'Jane Doe',
                'reference' => 'jane-doe',
                'platform' => PlatformType::FACEBOOK,
                'is_active' => true,
            ],
            [
                'name' => 'Jim Doe',
                'reference' => 'jim-doe',
                'platform' => PlatformType::TELEGRAM,
                'is_active' => true,
            ],
        ]);
        $organization->contacts()->attach($contacts->pluck('id'));

        $page = 1;
        $count = 10;
        $platform = 'instagram';
        $expected = [
            'data' => ContactResource::collection($contacts->where('platform', PlatformType::INSTAGRAM))->resolve(),
            'total' => $contacts->where('platform', PlatformType::INSTAGRAM)->count(),
            'page' => $page,
            'count' => $count,
        ];

        $response = $this->getJson("/api/public/organizations/{$organization->id}/contacts?p={$page}&c={$count}&platform={$platform}");

        $response->assertStatus(200);
        $response->assertExactJson($expected);
    }

    public function testFindOrganizationContactsByIdWithInvalidPageReturns404(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/organizations/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Organization not found']);
    }

    public function testFindOrganizationContactsByIdWithInvalidPageQueryParamReturns400(): void
    {
        $page = 'invalid-page';
        $count = 10;

        $response = $this->getJson("/api/public/organizations/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid page']);
    }

    public function testFindOrganizationContactsByIdWithInvalidCountQueryParamReturns400(): void
    {
        $page = 1;
        $count = 'invalid-count';

        $response = $this->getJson("/api/public/organizations/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid count']);
    }

    public function testFindOrganizationContactsByIdWithInvalidOrganizationIdParamReturns400(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/organizations/invalid-organization-id/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid organization ID']);
    }
}
