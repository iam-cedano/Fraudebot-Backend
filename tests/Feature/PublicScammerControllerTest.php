<?php

namespace Tests\Feature;

use App\Http\Resources\Public\ContactResource;
use App\Models\Contact;
use App\Models\Scammer;
use App\Http\Resources\Public\ScammerResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
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


    public function testFindContactsByScammerId(): void
    {
        /**
         * @var Scammer $scammer
         */
        $scammer = Scammer::factory()->create();
        $contact = Contact::factory()->create();

        $scammer->contacts()->attach($contact->id);

        $page = 1;
        $count = 10;

        $response = $this->getJson("/api/public/scammers/{$scammer->id}/contacts?p={$page}");

        $response->assertStatus(200);
        $response->assertExactJson([
            'data' => ContactResource::collection($scammer->contacts)->resolve(),
            'total' => $scammer->contacts->count(),
            'page' => $page,
            'count' => $count,
        ]);
    }
}
