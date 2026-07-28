<?php

namespace App\Repositories\Search;

use App\Domain\PaymentMethod\ValueObjects\AccountNumber;
use App\Domain\PaymentMethod\ValueObjects\CardNumber;
use App\Domain\PaymentMethod\ValueObjects\Clabe;
use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Models\Organization;
use App\Models\Report;
use App\Models\Scammer;
use App\Repositories\Scammer\ScammerCardRepository;
use Illuminate\Support\Collection;

class PublicSearchRepository implements SearchRepositoryInterface
{
    public function __construct(private ScammerCardRepository $scammerCardRepository)
    {
    }

    public function find(Clue $clue, int $page, int $count): Collection
    {
        if ($clue->getType() === ClueType::Nothing) {
            return collect([]);
        }

        $scammers = $this->findScammers($clue, $page, $count);

        if ($clue->getType() === ClueType::General) {
            return $this->mapScammersToReportCards($scammers);
        }

        $organizations = $this->findOrganizations($clue, $page, $count);

        return $this->mapScammersToReportCards($scammers)
            ->merge($this->mapOrganizationsToReportCards($organizations))
            ->take($count)
            ->values();
    }

    private function findScammers(Clue $clue, int $page, int $count): Collection
    {
        if ($clue->getType() === ClueType::IpAddress) {
            return $this->findScammersByContact($clue->getValue(), $page, $count);
        }

        if (!in_array($clue->getType(), [
            ClueType::Email,
            ClueType::CardNumber,
            ClueType::Clabe,
            ClueType::AccountNumber,
            ClueType::Phone,
            ClueType::Url,
            ClueType::General,
        ], true)) {
            return collect([]);
        }

        return $this->scammerCardRepository->find($clue, $page, $count, ['organizations']);
    }

    private function findScammersByContact(string $reference, int $page, int $count): Collection
    {
        return Scammer::query()
            ->whereHas('contacts', fn ($query) => $query->where('contact', '=', $reference))
            ->with(['organizations:id,name'])
            ->withCount('reports')
            ->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page)
            ->getCollection();
    }

    private function findOrganizations(Clue $clue, int $page, int $count): Collection
    {
        return match ($clue->getType()) {
            ClueType::General => $this->findOrganizationsByName($clue->getValue(), $page, $count),
            ClueType::Email, ClueType::Url, ClueType::IpAddress => $this->findOrganizationsByContact($clue->getValue(), $page, $count),
            ClueType::Phone => $this->findOrganizationsByPhone($clue->getValue(), $page, $count),
            ClueType::CardNumber => $this->findOrganizationsByPaymentReference(new CardNumber($clue->getValue()), $page, $count),
            ClueType::Clabe => $this->findOrganizationsByPaymentReference(new Clabe($clue->getValue()), $page, $count),
            ClueType::AccountNumber => $this->findOrganizationsByPaymentReference(new AccountNumber($clue->getValue()), $page, $count),
            default => collect(),
        };
    }

    private function findOrganizationsByName(string $reference, int $page, int $count): Collection
    {
        $parsedReference = trim(strip_tags($reference));

        return Organization::query()
            ->where('name', 'LIKE', "%{$parsedReference}%")
            ->withCount('reports')
            ->paginate($count, ['id', 'name', 'is_active'], 'page', $page)
            ->getCollection();
    }

    private function findOrganizationsByContact(string $reference, int $page, int $count): Collection
    {
        return Organization::query()
            ->whereHas('contacts', fn ($query) => $query->where('contact', '=', $reference))
            ->withCount('reports')
            ->paginate($count, ['id', 'name', 'is_active'], 'page', $page)
            ->getCollection();
    }

    private function findOrganizationsByPhone(string $phoneNumber, int $page, int $count): Collection
    {
        return Organization::query()
            ->whereHas('paymentMethods', fn ($query) => $query->where('reference', '=', $phoneNumber))
            ->withCount('reports')
            ->paginate($count, ['id', 'name', 'is_active'], 'page', $page)
            ->getCollection();
    }

    private function findOrganizationsByPaymentReference(
        CardNumber|Clabe|AccountNumber $reference,
        int $page,
        int $count,
    ): Collection {
        return Organization::query()
            ->whereHas('paymentMethods', fn ($query) => $query->where('reference', '=', (string) $reference))
            ->withCount('reports')
            ->paginate($count, ['id', 'name', 'is_active'], 'page', $page)
            ->getCollection();
    }

    /**
     * @param Collection<int, Scammer> $scammers
     */
    private function mapScammersToReportCards(Collection $scammers): Collection
    {
        if ($scammers->isEmpty()) {
            return collect([]);
        }

        $productsByScammerId = $this->getProductNamesGroupedByScammerId(
            $scammers->pluck('id')->all(),
        );

        return $scammers->map(fn (Scammer $scammer): object => (object) [
            'id' => $scammer->id,
            'name' => $scammer->name,
            'reports' => $scammer->reports_count ?? $scammer->reports()->count(),
            'iso_country' => $scammer->iso_country,
            'is_active' => $scammer->is_active,
            'organizations' => $scammer->relationLoaded('organizations')
                ? $scammer->organizations->pluck('name')->values()->all()
                : [],
            'products' => $productsByScammerId[$scammer->id] ?? [],
            'type' => 'scammer',
        ]);
    }

    /**
     * @param Collection<int, Organization> $organizations
     */
    private function mapOrganizationsToReportCards(Collection $organizations): Collection
    {
        if ($organizations->isEmpty()) {
            return collect([]);
        }

        $productsByOrganizationId = $this->getProductNamesGroupedByOrganizationId(
            $organizations->pluck('id')->all(),
        );

        return $organizations->map(fn (Organization $organization): object => (object) [
            'id' => $organization->id,
            'name' => $organization->name,
            'reports' => $organization->reports_count ?? $organization->reports()->count(),
            'iso_country' => null,
            'is_active' => $organization->is_active,
            'organizations' => [],
            'products' => $productsByOrganizationId[$organization->id] ?? [],
            'type' => 'organization',
        ]);
    }

    /**
     * @param array<int, int> $scammerIds
     * @return array<int, array<int, string>>
     */
    private function getProductNamesGroupedByScammerId(array $scammerIds): array
    {
        return Report::query()
            ->whereIn('scammer_id', $scammerIds)
            ->whereNotNull('product_id')
            ->with('product:id,name')
            ->get()
            ->groupBy('scammer_id')
            ->map(fn (Collection $reports): array => $reports
                ->pluck('product.name')
                ->filter()
                ->unique()
                ->values()
                ->all())
            ->all();
    }

    /**
     * @param array<int, int> $organizationIds
     * @return array<int, array<int, string>>
     */
    private function getProductNamesGroupedByOrganizationId(array $organizationIds): array
    {
        return Report::query()
            ->whereIn('organization_id', $organizationIds)
            ->whereNotNull('product_id')
            ->with('product:id,name')
            ->get()
            ->groupBy('organization_id')
            ->map(fn (Collection $reports): array => $reports
                ->pluck('product.name')
                ->filter()
                ->unique()
                ->values()
                ->all())
            ->all();
    }
}
