<?php

namespace Tests\Feature;

use App\Domain\Contact\Enums\PlatformType;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Http\Resources\Public\ReportCardResource;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Report;
use App\Models\Scammer;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PublicReportControllerTest extends TestCase
{
    public function test_report_search_by_clabe(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            '012345678901234567',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_card_number(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            '4152313732125521',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_account_number(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            '0123456789',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_email(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            'test@example.com',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_phone(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            '525512345678',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_url(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            'https://example.com',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_domain(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            'example.com',
            $this->expectedBoth($fixtures),
        );
    }

    public function test_report_search_by_ip_address(): void
    {
        $this->assertReportSearch('192.168.1.1', []);
    }

    public function test_report_search_by_empty_query(): void
    {
        $this->assertReportSearch('', []);
    }

    public function test_report_search_by_null_query(): void
    {
        $this->assertReportSearch(null, []);
    }

    public function test_report_search_by_general_query(): void
    {
        $fixtures = $this->seedDefaultSearchFixtures();

        $this->assertReportSearch(
            'John Doe',
            [$fixtures['scammer']],
        );
    }

    /**
     * @param  array<int, Scammer|Organization>  $expectedModels
     */
    private function assertReportSearch(
        ?string $query,
        array $expectedModels,
        int $page = 1,
        int $count = 10,
    ): void {
        $params = ['p' => $page, 'c' => $count];
        if ($query !== null) {
            $params['q'] = $query;
        }

        $response = $this->getJson('/api/public/reports?'.http_build_query($params));

        $response->assertOk();
        $response->assertExactJson([
            'data' => ReportCardResource::collection(collect($expectedModels))->resolve(),
            'total' => count($expectedModels),
            'page' => $page,
            'count' => $count,
        ]);
    }

    /**
     * @return array{scammer: Scammer, organization: Organization}
     */
    private function seedDefaultSearchFixtures(): array
    {
        $organization = Organization::factory()->create([
            'name' => 'Ecohuertas',
            'country' => 'MX',
            'updated_at' => now()->subDay(),
        ]);

        $scammer = Scammer::factory()->create([
            'name' => 'John Doe',
            'country' => 'MX',
        ]);

        $scammer->organizations()->attach($organization);

        $this->attachReportsWithProducts($scammer, 2);
        $this->attachReportsWithProducts($organization, 5);
        $this->attachSharedPaymentMethods($scammer, $organization);
        $this->attachSharedContacts($scammer, $organization);

        return [
            'scammer' => $this->loadSearchCard($scammer),
            'organization' => $this->loadSearchCard($organization),
        ];
    }

    /**
     * @param  array{scammer: Scammer, organization: Organization}  $fixtures
     * @return array<int, Scammer|Organization>
     */
    private function expectedBoth(array $fixtures): array
    {
        return $this->sortSearchCards([
            $fixtures['scammer'],
            $fixtures['organization'],
        ]);
    }

    private function attachReportsWithProducts(Scammer|Organization $entity, int $reportCount): void
    {
        $products = Product::factory()->count(3)->create();
        $reports = Report::factory()->count($reportCount)->create();

        foreach ($reports as $report) {
            $entity->reports()->attach($report);
            $report->products()->attach($products);
        }
    }

    private function attachSharedPaymentMethods(Scammer $scammer, Organization $organization): void
    {
        $references = [
            [PaymentMethodType::CLABE, '012345678901234567'],
            [PaymentMethodType::CARD_NUMBER, '4152313732125521'],
            [PaymentMethodType::ACCOUNT_NUMBER, '0123456789'],
        ];

        foreach ($references as [$type, $reference]) {
            $paymentMethod = PaymentMethod::factory()->create([
                'type' => $type,
                'reference' => $reference,
            ]);

            $scammer->paymentMethods()->attach($paymentMethod);
            $organization->paymentMethods()->attach($paymentMethod);
        }
    }

    private function attachSharedContacts(Scammer $scammer, Organization $organization): void
    {
        $contacts = [
            [PlatformType::EMAIL, 'test@example.com'],
            [PlatformType::CELLPHONE, '525512345678'],
            [PlatformType::URL, 'https://example.com'],
            [PlatformType::URL, 'example.com'],
        ];

        foreach ($contacts as [$platform, $reference]) {
            $contact = Contact::factory()->create([
                'platform' => $platform,
                'reference' => $reference,
            ]);

            $scammer->contacts()->attach($contact);
            $organization->contacts()->attach($contact);
        }
    }

    private function loadSearchCard(Scammer|Organization $model): Scammer|Organization
    {
        if ($model instanceof Scammer) {
            return Scammer::query()
                ->whereKey($model->id)
                ->where('is_active', true)
                ->select(['id', 'name', 'country', 'is_active', 'created_at', 'updated_at'])
                ->with(['organizations', 'reports' => fn ($query) => $query->where('is_active', true)->with('products')])
                ->withCount(['reports' => fn ($query) => $query->where('is_active', true)])
                ->firstOrFail();
        }

        return Organization::query()
            ->whereKey($model->id)
            ->where('is_active', true)
            ->select(['id', 'name', 'country', 'is_active', 'created_at', 'updated_at'])
            ->with(['reports' => fn ($query) => $query->where('is_active', true)->with('products')])
            ->withCount(['reports' => fn ($query) => $query->where('is_active', true)])
            ->firstOrFail();
    }

    /**
     * @param  array<int, Scammer|Organization>  $models
     * @return array<int, Scammer|Organization>
     */
    private function sortSearchCards(array $models): array
    {
        return Collection::make($models)
            ->sort(function (Scammer|Organization $left, Scammer|Organization $right): int {
                $updatedAtCompare = $right->updated_at <=> $left->updated_at;

                return $updatedAtCompare !== 0 ? $updatedAtCompare : $right->id <=> $left->id;
            })
            ->values()
            ->all();
    }
}
