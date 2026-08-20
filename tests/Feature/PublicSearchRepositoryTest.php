<?php

namespace Tests\Feature;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Models\Scammer;
use App\Repositories\Organization\OrganizationCardRepositoryInterface;
use App\Repositories\Scammer\ScammerCardRepositoryInterface;
use App\Repositories\Search\PublicSearchRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSearchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testIpAddressClueDoesNotCrash(): void
    {
        Scammer::factory()->create(['name' => 'Some Name']);

        $result = $this->repository()->find(new Clue('192.168.1.1'), 1, 10);

        $this->assertSame(0, $result->total);
        $this->assertCount(0, $result->items);
    }

    public function testTotalReflectsAllMatchesNotJustTheCurrentPage(): void
    {
        Scammer::factory()->count(15)->create(['name' => 'Jane Doe']);

        $result = $this->repository()->find(new Clue('Jane Doe'), 1, 10);

        $this->assertSame(15, $result->total);
        $this->assertCount(10, $result->items);
    }

    public function testSoftDeletedScammersAreExcludedFromSearch(): void
    {
        $scammer = Scammer::factory()->create(['name' => 'Deleted Guy']);
        $scammer->delete();

        $result = $this->repository()->find(new Clue('Deleted Guy'), 1, 10);

        $this->assertSame(0, $result->total);
        $this->assertCount(0, $result->items);
    }

    public function testCacheIsInvalidatedWhenAMatchingScammerIsCreated(): void
    {
        Scammer::factory()->create(['name' => 'Cache Target']);

        $first = $this->repository()->find(new Clue('Cache Target'), 1, 10);
        $this->assertSame(1, $first->total);

        Scammer::factory()->create(['name' => 'Cache Target']);

        $second = $this->repository()->find(new Clue('Cache Target'), 1, 10);
        $this->assertSame(2, $second->total);
    }

    private function repository(): PublicSearchRepository
    {
        return new PublicSearchRepository(
            app(ScammerCardRepositoryInterface::class),
            app(OrganizationCardRepositoryInterface::class),
        );
    }
}
