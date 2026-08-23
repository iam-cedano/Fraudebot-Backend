<?php

namespace Tests\Feature;

use App\Domain\Contact\Enums\PlatformType;
use App\Http\Resources\Public\ContactResource;
use App\Http\Resources\Public\ScammerResource;
use App\Models\Contact;
use App\Models\Scammer;
use Tests\TestCase;

class PublicScammerControllerTest extends TestCase
{
    public function testFindScammerById(): void
    {
        /**
         * @var Scammer $scammer
         */
        $scammer = Scammer::factory()->create();

        $response = $this->getJson("/api/public/scammers/{$scammer->id}");

        $response->assertStatus(200);
        $response->assertExactJson(ScammerResource::make($scammer)->resolve());
    }

    public function testFindScammerByInvalidIdReturns400(): void
    {
        $response = $this->getJson("/api/public/scammers/9999999999999999999999999999999999999999");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID']);
    }

    public function testFindScammerByNonExistentIdReturns404(): void
    {
        $response = $this->getJson("/api/public/scammers/0");

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Scammer not found']);
    }

    public function testFindScammerContactsById(): void
    {
        $scammer = Scammer::factory()->create();

        $contacts = Contact::factory()->count(3)->create();

        $scammer->contacts()->attach($contacts->pluck('id'));

        $page = 1;
        $count = 10;
        $expected = [
            'data' => ContactResource::collection($contacts)->resolve(),
            'total' => $contacts->count(),
            'page' => $page,
            'count' => $count,
        ];

        $response = $this->getJson("/api/public/scammers/{$scammer->id}/contacts?p={$page}&c={$count}");

        $response->assertStatus(200);
        $response->assertExactJson($expected);
    }

    public function testFindScammerContactsByIdWithPlatformQueryParam(): void
    {
        $scammer = Scammer::factory()->create();
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
        $scammer->contacts()->attach($contacts->pluck('id'));

        $page = 1;
        $count = 10;
        $platform = 'instagram';
        $expected = [
            'data' => ContactResource::collection($contacts->where('platform', PlatformType::INSTAGRAM))->resolve(),
            'total' => $contacts->where('platform', PlatformType::INSTAGRAM)->count(),
            'page' => $page,
            'count' => $count,
        ];

        $response = $this->getJson("/api/public/scammers/{$scammer->id}/contacts?p={$page}&c={$count}&platform={$platform}");

        $response->assertStatus(200);
        $response->assertExactJson($expected);
    }

    public function testFindScammerContactsByIdWithInvalidPageReturns404(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Contacts not found']);
    }

    public function testFindScammerContactsByIdWithInvalidPageQueryParamReturns400(): void
    {
        $page = 'invalid-page';
        $count = 10;

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }

    public function testFindScammerContactsByIdWithInvalidCountQueryParamReturns400(): void
    {
        $page = 1;
        $count = 'invalid-count';

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }

    public function testFindScammerContactsByIdWithInvalidScammerIdParamReturns400(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/scammers/invalid-scammer-id/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }
}
