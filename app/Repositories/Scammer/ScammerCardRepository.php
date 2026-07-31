<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use App\Models\Scammer;
use App\Repositories\Search\ClueSearchInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ScammerCardRepository implements ScammerCardRepositoryInterface, ClueSearchInterface
{
    public function matchQuery(Clue $clue): ?Builder
    {
        return match ($clue->getType()) {
            ClueType::Email => $this->matchByEmail($clue->getValue()),
            ClueType::CardNumber => $this->matchByCardNumber($clue->getValue()),
            ClueType::Clabe => $this->matchByClabe($clue->getValue()),
            ClueType::AccountNumber => $this->matchByAccountNumber($clue->getValue()),
            ClueType::Phone => $this->matchByPhoneNumber($clue->getValue()),
            ClueType::Url => $this->matchByUrl($clue->getValue()),
            ClueType::IpAddress => $this->matchByIpAddress($clue->getValue()),
            ClueType::Username => $this->matchByUsername($clue->getValue()),
            ClueType::Name => $this->matchByName($clue->getValue()),
            ClueType::Nothing => null,
        };
    }

    public function hydrate(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Scammer::query()
            ->whereIn('id', $ids)
            ->with(['organizations', 'reports.product'])
            ->get(['id', 'name', 'country', 'is_active', 'created_at', 'updated_at'])
            ->keyBy('id');
    }

    public function matchByName(string $name): ?Builder
    {
        return Scammer::query()->where('name', 'LIKE', "%{$name}%");
    }

    public function matchByCardNumber(string $cardNumber): ?Builder
    {
        return null;
    }

    public function matchByClabe(string $clabe): ?Builder
    {
        return null;
    }

    public function matchByAccountNumber(string $accountNumber): ?Builder
    {
        return null;
    }

    public function matchByEmail(string $email): ?Builder
    {
        return null;
    }

    public function matchByPhoneNumber(string $phoneNumber): ?Builder
    {
        return null;
    }

    public function matchByUrl(string $url): ?Builder
    {
        return null;
    }

    public function matchByIpAddress(string $ipAddress): ?Builder
    {
        return null;
    }

    public function matchByUsername(string $username): ?Builder
    {
        return null;
    }
}
