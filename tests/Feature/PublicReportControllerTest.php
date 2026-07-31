<?php

namespace Tests\Feature;

use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Search\ValueObjects\CardSearchResult;
use App\Http\Controllers\Public\ReportController;
use App\Http\Resources\Public\ReportCardResource;
use App\Models\Organization;
use App\Models\Scammer;
use App\Repositories\Search\SearchRepositoryInterface;
use Illuminate\Http\Request;
use Tests\TestCase;

use function count;

class PublicReportControllerTest extends TestCase
{
    public function testReportSearchByClabe(): void
    {
        $this->assertReportSearch(
            '0123450123456789',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByCardNumber(): void
    {
        $this->assertReportSearch(
            '4152313732125521',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByAccountNumber(): void
    {
        $this->assertReportSearch(
            '0123456789',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByEmail(): void
    {
        $this->assertReportSearch(
            'test@example.com',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByPhone(): void
    {
        $this->assertReportSearch(
            '525512345678',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByUrl(): void
    {
        $this->assertReportSearch(
            'https://example.com',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByDomain(): void
    {
        $this->assertReportSearch(
            'example.com',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByIpAddress(): void
    {
        $this->assertReportSearch(
            '192.168.1.1',
            $this->defaultReportData(),
        );
    }

    public function testReportSearchByEmptyQuery(): void
    {
        $this->assertReportSearch('', []);
    }

    public function testReportSearchByNullQuery(): void
    {
        $this->assertReportSearch(null, []);
    }

    public function testReportSearchByGeneralQuery(): void
    {
        $this->assertReportSearch(
            'John Doe',
            [$this->defaultReportData()[0]],
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultReportData(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'John Doe',
                'reports' => collect([
                    [
                        'id' => 1,
                        'product' => [
                            'name' => 'Invertions',
                        ],
                    ],
                ]),
                'country' => 'MX',
                'products' => collect(['Invertions', 'Crypto', 'NFT']),
                'organizations' => collect(['Ecohuertas']),
                'type' => 'scammer',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Ecohuertas',
                'reports' => collect([
                    [
                        'id' => 1,
                        'product' => [
                            'name' => 'Crypto',
                        ],
                    ],
                    [
                        'id' => 2,
                        'product' => [
                            'name' => 'NFT',
                        ],
                    ],
                    [
                        'id' => 3,
                        'product' => [
                            'name' => 'Invertions',
                        ],
                    ]
                ]),
                'country' => 'MX',
                'products' => collect(['Crypto', 'NFT', 'Invertions']),
                'type' => 'organization',
                'is_active' => true,
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $expectedData
     */
    private function assertReportSearch(
        string|null $query,
        array $expectedData,
        int $page = 1,
        int $count = 10,
    ): void {
        $searchRepositoryMock = $this->createMock(SearchRepositoryInterface::class);

        $clueMatcher = $query === null
            ? $this->callback(fn(Clue $clue) => $clue->getType() === ClueType::Nothing)
            : $this->callback(fn(Clue $clue) => $clue->getValue() === $query);

        $models = $this->makeReportModels($expectedData);

        $searchRepositoryMock->expects($this->once())
            ->method('find')
            ->with(
                $clueMatcher,
                $this->equalTo($page),
                $this->equalTo($count),
            )
            ->willReturn(new CardSearchResult(collect($models), count($models)));

        $requestParams = ['p' => $page, 'c' => $count];
        if ($query !== null) {
            $requestParams['q'] = $query;
        }

        $request = Request::create('/', 'GET', $requestParams);
        $response = (new ReportController($searchRepositoryMock))->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            ReportCardResource::collection(collect($models))->resolve(),
            $response->getData(true)['data'],
        );
        $this->assertEquals($page, $response->getData(true)['page']);
        $this->assertEquals($count, $response->getData(true)['count']);
        $this->assertEquals(count($expectedData), $response->getData(true)['total']);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, Scammer|Organization>
     */
    private function makeReportModels(array $items): array
    {
        return array_map(function (array $item): Scammer|Organization {
            $model = $item['type'] === 'scammer'
                ? new Scammer()
                : new Organization();

            $model->forceFill([
                'id' => $item['id'],
                'name' => $item['name'],
                'country' => $item['country'],
                'is_active' => $item['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $model->reports = $item['reports'];
            $model->products = $item['products'];

            if ($item['type'] === 'scammer') {
                $model->organizations = $item['organizations'];
            }

            return $model;
        }, $items);
    }
}
