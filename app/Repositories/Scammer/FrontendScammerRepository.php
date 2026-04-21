<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use App\Models\Scammer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FrontendScammerRepository implements ScammerRepositoryInterface {
    public function findAll(int $page, int $count): Collection {
        $cacheKey = "scammers:all:$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($page, $count) {
            $responses = Scammer::paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);
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
        $parsedCardNumber = preg_replace('/\D/', '', $cardNumber);

        $cacheKey = "scammers:pm:card:" . hash('md5', $parsedCardNumber) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedCardNumber, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedCardNumber) {
                $query->where('reference', 'LIKE', "%$parsedCardNumber%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByClabe(string $clabe, int $page, int $count): Collection {
        $parsedClabe = trim($clabe);
        $cacheKey = "scammers:pm:clabe:" . hash('md5', $parsedClabe) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedClabe, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedClabe) {
                $query->where('reference', 'LIKE', "%$parsedClabe%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByAccountNumber(string $accountNumber, int $page, int $count): Collection {
        $parsedAccountNumber = trim($accountNumber);
        $cacheKey = "scammers:pm:account:" . hash('md5', $parsedAccountNumber) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedAccountNumber, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedAccountNumber) {
                $query->where('reference', 'LIKE', "%$parsedAccountNumber%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByEmail(string $email, int $page, int $count): Collection {
        $parsedEmail = trim($email);
        $cacheKey = "scammers:pm:email:" . hash('md5', $parsedEmail) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedEmail, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedEmail) {
                $query->where('reference', 'LIKE', "%$parsedEmail%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByPhoneNumber(string $phoneNumber, int $page, int $count): Collection {
        $parsedPhoneNumber = trim(preg_replace('/\D/', '', $phoneNumber));
        $cacheKey = "scammers:pm:phone:" . hash('md5', $parsedPhoneNumber) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedPhoneNumber, $page, $count) {
            $scammers = Scammer::whereHas('paymentMethods', function ($query) use ($parsedPhoneNumber) {
                $query->where('reference', 'LIKE', "%$parsedPhoneNumber%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }

    private function findScammerByUrl(string $url, int $page, int $count): Collection {
        $parsedUrl = trim($url);
        $cacheKey = "scammers:profile:url:" . hash('md5', $parsedUrl) . ":$page:$count";

        return Cache::remember($cacheKey, 3600, function () use ($parsedUrl, $page, $count) {
            $scammers = Scammer::whereHas('profiles', function ($query) use ($parsedUrl) {
                $query->where('social_media', 'LIKE', "%$parsedUrl%");
            })->paginate($count, ['name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection();
        });
    }
}