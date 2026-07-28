<?php

namespace App\Repositories\Organization;

use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Repositories\Search\ClueSearchInterface;
use Illuminate\Support\Collection;
use App\Models\Organization;

class OrganizationCardRepository implements OrganizationCardRepositoryInterface, ClueSearchInterface
{
    public function find(Clue $clue, int $page, int $count): Collection
    {
        return match ($clue->getType()) {
            ClueType::Name => $this->findByName($clue->getValue(), $page, $count),
            ClueType::Email => $this->findByEmail($clue->getValue(), $page, $count),
            ClueType::Url => $this->findByUrl($clue->getValue(), $page, $count),
            ClueType::CardNumber => $this->findByCardNumber($clue->getValue(), $page, $count),
            ClueType::Clabe => $this->findByClabe($clue->getValue(), $page, $count),
            ClueType::AccountNumber => $this->findByAccountNumber($clue->getValue(), $page, $count),
            ClueType::Phone => $this->findByPhoneNumber($clue->getValue(), $page, $count),
        };
    }

    public function findByName(string $name, int $page, int $count): Collection
    {
        return Organization::query()
            ->where('name', 'LIKE', "%{$name}%")
            ->paginate($count, ['*'], 'page', $page)
            ->getCollection();
    }

    public function findByEmail(string $email, int $page, int $count): Collection
    {
        return collect([]);
    }

    public function findByUrl(string $url, int $page, int $count): Collection
    {
        return collect([]);
    }

    public function findByCardNumber(string $cardNumber, int $page, int $count): Collection
    {
        return collect([]);
    }

    public function findByClabe(string $clabe, int $page, int $count): Collection
    {
        return collect([]);
    }

    public function findByAccountNumber(string $accountNumber, int $page, int $count): Collection
    {
        return collect([]);
    }

    public function findByPhoneNumber(string $phoneNumber, int $page, int $count): Collection
    {
        return collect([]);
    }
}
