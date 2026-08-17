<?php

namespace App\Repositories\Organization;

use App\Domain\Contact\Enums\PlatformType;
use App\Domain\PaymentMethod\Enums\PaymentMethodType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use App\Models\Organization;
use App\Repositories\Search\ClueSearchInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrganizationCardRepository implements OrganizationCardRepositoryInterface, ClueSearchInterface
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

        return Organization::query()
            ->whereIn('id', $ids)
            ->with(['reports.product'])
            ->get(['id', 'name', 'country', 'is_active', 'created_at', 'updated_at'])
            ->keyBy('id');
    }

    public function matchByName(string $name): ?Builder
    {
        return Organization::query()->where('name', 'LIKE', "%{$name}%");
    }

    public function matchByCardNumber(string $cardNumber): ?Builder
    {
        return Organization::query()
            ->whereRelation('paymentMethods', 'type', PaymentMethodType::CARD_NUMBER)
            ->whereRelation('paymentMethods', 'reference', $cardNumber);
    }

    public function matchByClabe(string $clabe): ?Builder
    {
        return Organization::query()
            ->whereRelation('paymentMethods', 'type', PaymentMethodType::CLABE)
            ->whereRelation('paymentMethods', 'reference', $clabe);
    }

    public function matchByAccountNumber(string $accountNumber): ?Builder
    {
        return Organization::query()
            ->whereRelation('paymentMethods', 'type', PaymentMethodType::ACCOUNT_NUMBER)
            ->whereRelation('paymentMethods', 'reference', $accountNumber);
    }

    public function matchByEmail(string $email): ?Builder
    {
        return Organization::query()
            ->whereRelation('contacts', 'platform', PlatformType::EMAIL)
            ->whereRelation('contacts', 'reference', $email);
    }

    public function matchByPhoneNumber(string $phoneNumber): ?Builder
    {
        return Organization::query()
            ->whereRelation('contacts', 'platform', PlatformType::CELLPHONE)
            ->whereRelation('contacts', 'reference', $phoneNumber);
    }

    public function matchByUrl(string $url): ?Builder
    {
        return Organization::query()
            ->whereRelation('contacts', 'platform', PlatformType::URL)
            ->whereRelation('contacts', 'reference', $url);
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
