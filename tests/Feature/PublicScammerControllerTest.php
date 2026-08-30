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
    public function test_find_scammer_by_id(): void
    {
        /**
         * @var Scammer $scammer
         */
        $scammer = Scammer::factory()->create();

        $response = $this->getJson("/api/public/scammers/{$scammer->id}");

        $response->assertStatus(200);
        $response->assertExactJson(ScammerResource::make($scammer)->resolve());
    }

    public function test_find_scammer_by_invalid_id_returns400(): void
    {
        $response = $this->getJson('/api/public/scammers/9999999999999999999999999999999999999999');

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID']);
    }

    public function test_find_scammer_by_non_existent_id_returns404(): void
    {
        $response = $this->getJson('/api/public/scammers/0');

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Scammer not found']);
    }

    public function test_find_scammer_contacts_by_id(): void
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

    public function test_find_scammer_contacts_by_id_with_platform_query_param(): void
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

    public function test_contacts_total_reflects_all_matching_rows(): void
    {
        $scammer = Scammer::factory()->create();
        $contacts = Contact::factory()->count(15)->create();
        $scammer->contacts()->attach($contacts);

        $this->getJson("/api/public/scammers/{$scammer->id}/contacts?p=1&c=10")
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('total', 15);
    }

    public function test_contact_pagination_is_bounded(): void
    {
        $scammer = Scammer::factory()->create();

        $this->getJson("/api/public/scammers/{$scammer->id}/contacts?p=0&c=101")
            ->assertBadRequest();
    }

    public function test_contact_changes_invalidate_cached_public_contacts(): void
    {
        $scammer = Scammer::factory()->create();
        $contact = Contact::factory()->create(['name' => 'Before']);
        $scammer->contacts()->attach($contact);

        $this->getJson("/api/public/scammers/{$scammer->id}/contacts")
            ->assertJsonPath('data.0.name', 'Before');

        $contact->update(['name' => 'After']);

        $this->getJson("/api/public/scammers/{$scammer->id}/contacts")
            ->assertJsonPath('data.0.name', 'After');
    }

    public function test_find_scammer_contacts_by_id_with_invalid_page_returns404(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Scammer contacts not found']);
    }

    public function test_find_scammer_contacts_by_id_with_invalid_page_query_param_returns400(): void
    {
        $page = 'invalid-page';
        $count = 10;

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }

    public function test_find_scammer_contacts_by_id_with_invalid_count_query_param_returns400(): void
    {
        $page = 1;
        $count = 'invalid-count';

        $response = $this->getJson("/api/public/scammers/1/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }

    public function test_find_scammer_contacts_by_id_with_invalid_scammer_id_param_returns400(): void
    {
        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/scammers/invalid-scammer-id/contacts?p={$page}&c={$count}");

        $response->assertStatus(400);
        $response->assertExactJson(['message' => 'Invalid scammer ID, page or count']);
    }
}
