<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use App\Domain\ScammerPaymentMethod\ValueObjects\AccountNumber;
use App\Domain\ScammerPaymentMethod\ValueObjects\CardNumber;
use App\Domain\ScammerPaymentMethod\ValueObjects\Clabe;
use App\Domain\ScammerProfile\ValueObjects\Email;
use App\Domain\ScammerProfile\ValueObjects\PhoneNumber;
use App\Domain\ScammerProfile\ValueObjects\URL;
use App\Models\Scammer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FrontendScammerRepository implements ScammerRepositoryInterface {
    public function findAll(int $page, int $count): Collection {
        $cacheKey = "scammers:all:$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($page, $count) {
            $responses = Scammer::paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);
            return $responses->getCollection();
        });
    }

    public function find(Clue $clue, int $page, int $count): Collection {
        return match($clue->getType()) {
            ClueType::Email => $this->findScammerByEmail($clue->getValue(), $page, $count),
            ClueType::CardNumber => $this->findScammerByCardNumber($clue->getValue(), $page, $count),
            ClueType::Clabe => $this->findScammerByClabe($clue->getValue(), $page, $count),
            ClueType::AccountNumber => $this->findScammerByAccountNumber($clue->getValue(), $page, $count),
            ClueType::Phone => $this->findScammerByPhoneNumber($clue->getValue(), $page, $count),
            ClueType::Url => $this->findScammerByUrl($clue->getValue(), $page, $count),
            ClueType::General => $this->findScammerByGeneral($clue->getValue(), $page, $count),
        };
    }

    private function findScammerByGeneral(string $reference, int $page, int $count): Collection {
        $parsedReference = trim(strip_tags($reference));

        $scammers = Scammer::where('name', 'LIKE', "%$parsedReference%")
            ->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

        return $scammers->getCollection();
    }

    private function findScammerByCardNumber(string $cardNumber, int $page, int $count): Collection {
        $cardNumberObj = new CardNumber($cardNumber);

        $cacheKey = "scammers:pm:card:" . hash('md5', $cardNumberObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($cardNumberObj, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($cardNumberObj) {
                $query->where('reference', '=', $cardNumberObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByClabe(string $clabe, int $page, int $count): Collection {
        $clabeObj = new Clabe($clabe);
        $cacheKey = "scammers:pm:clabe:" . hash('md5', $clabeObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($clabeObj, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($clabeObj) {
                $query->where('reference', '=', $clabeObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByAccountNumber(string $accountNumber, int $page, int $count): Collection {
        $parsedAccountObj = new AccountNumber($accountNumber);
        $cacheKey = "scammers:pm:account:" . hash('md5', $parsedAccountObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedAccountObj, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedAccountObj) {
                $query->where('reference', '=', $parsedAccountObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByEmail(string $email, int $page, int $count): Collection {
        $emailObj = new Email($email);
        $cacheKey = "scammers:pm:email:" . hash('md5', $emailObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($emailObj, $page, $count) {
            $scammers = Scammer::whereHas('profiles', function ($query) use ($emailObj) {
                $query->where('contact', '=', $emailObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByPhoneNumber(string $phoneNumber, int $page, int $count): Collection {
        $phoneNumberObj = new PhoneNumber($phoneNumber);
        $cacheKey = "scammers:pm:phone:" . hash('md5', $phoneNumberObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($phoneNumberObj, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($phoneNumberObj) {
                $query->where('reference', '=', $phoneNumberObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByUrl(string $url, int $page, int $count): Collection {
        $urlObj = new URL($url);
        $cacheKey = "scammers:profile:url:" . hash('md5', $urlObj) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($urlObj, $page, $count) {
            $scammers = Scammer::whereHas('profiles', function ($query) use ($urlObj) {
                $query->where('contact', '=', $urlObj);
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }
}