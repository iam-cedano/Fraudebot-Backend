<?php
namespace App\Repositories\Scammer;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use App\Domain\PaymentMethod\ValueObjects\AccountNumber;
use App\Domain\PaymentMethod\ValueObjects\CardNumber;
use App\Domain\PaymentMethod\ValueObjects\Clabe;
use App\Domain\ScammerProfile\ValueObjects\Email;
use App\Domain\ScammerProfile\ValueObjects\PhoneNumber;
use App\Domain\ScammerProfile\ValueObjects\URL;
use App\Models\Scammer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FrontendScammerRepository implements ScammerRepositoryInterface
{
    private function getEagerLoads(array $relationships): array
    {
        $eagerLoads = [];
        foreach ($relationships as $relationship) {
            if ($relationship === 'profiles') {
                $eagerLoads['profiles'] = function ($q) {
                    $q->select('id', 'scammer_id', 'name', 'platform', 'contact');
                };
            } elseif ($relationship === 'paymentMethods') {
                $eagerLoads['paymentMethods'] = function ($q) {
                    $q->select('id', 'scammer_id', 'payment_type', 'reference');
                };
            } elseif ($relationship === 'organizations') {
                $eagerLoads['organizations'] = function ($q) {
                    $q->select('organizations.id', 'name', 'description', 'is_active');
                };
            }
        }
        return $eagerLoads;
    }

    public function findAll(int $page, int $count, $relationships = []): Collection
    {
        $cacheKey = "scammers:all:$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($page, $count, $relationships) {
            $query = Scammer::query();

            $eagerLoads = $this->getEagerLoads($relationships);

            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $paginator = $query->paginate(
                $count,
                ['id', 'name', 'iso_country', 'is_active'],
                'page',
                $page
            );

            $scammers = $paginator->getCollection();


            return $scammers;
        });
    }

    public function find(Clue $clue, int $page, int $count, $relationships = []): Collection
    {
        return match ($clue->getType()) {
            ClueType::Email => $this->findScammerByEmail($clue->getValue(), $page, $count, $relationships),
            ClueType::CardNumber => $this->findScammerByCardNumber($clue->getValue(), $page, $count, $relationships),
            ClueType::Clabe => $this->findScammerByClabe($clue->getValue(), $page, $count, $relationships),
            ClueType::AccountNumber => $this->findScammerByAccountNumber($clue->getValue(), $page, $count, $relationships),
            ClueType::Phone => $this->findScammerByPhoneNumber($clue->getValue(), $page, $count, $relationships),
            ClueType::Url => $this->findScammerByUrl($clue->getValue(), $page, $count, $relationships),
            ClueType::General => $this->findScammerByGeneral($clue->getValue(), $page, $count, $relationships),
        };
    }

    private function findScammerByGeneral(string $reference, int $page, int $count, array $relationships): Collection
    {
        $parsedReference = trim(strip_tags($reference));

        $query = Scammer::where('name', 'LIKE', "%$parsedReference%");
        
        $eagerLoads = $this->getEagerLoads($relationships);
        if (!empty($eagerLoads)) {
            $query->with($eagerLoads);
        }

        $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);
        
        $collection = $scammers->getCollection();
        $collection->each(function ($scammer) {
            if ($scammer->relationLoaded('organizations')) {
                $scammer->organizations->makeHidden('pivot');
            }
        });

        return $collection;
    }

    private function findScammerByCardNumber(string $cardNumber, int $page, int $count, array $relationships): Collection
    {
        $cardNumberObj = new CardNumber($cardNumber);

        $cacheKey = "scammers:pm:card:" . hash('md5', $cardNumberObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($cardNumberObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('paymentMethods', function ($query) use ($cardNumberObj) {
                $query->where('reference', '=', $cardNumberObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }

    private function findScammerByClabe(string $clabe, int $page, int $count, array $relationships): Collection
    {
        $clabeObj = new Clabe($clabe);
        $cacheKey = "scammers:pm:clabe:" . hash('md5', $clabeObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($clabeObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('paymentMethods', function ($query) use ($clabeObj) {
                $query->where('reference', '=', $clabeObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }

    private function findScammerByAccountNumber(string $accountNumber, int $page, int $count, array $relationships): Collection
    {
        $parsedAccountObj = new AccountNumber($accountNumber);
        $cacheKey = "scammers:pm:account:" . hash('md5', $parsedAccountObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($parsedAccountObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('paymentMethods', function ($query) use ($parsedAccountObj) {
                $query->where('reference', '=', $parsedAccountObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }

    private function findScammerByEmail(string $email, int $page, int $count, array $relationships): Collection
    {
        $emailObj = new Email($email);
        $cacheKey = "scammers:pm:email:" . hash('md5', $emailObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($emailObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('profiles', function ($query) use ($emailObj) {
                $query->where('contact', '=', $emailObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }

    private function findScammerByPhoneNumber(string $phoneNumber, int $page, int $count, array $relationships): Collection
    {
        $phoneNumberObj = new PhoneNumber($phoneNumber);
        $cacheKey = "scammers:pm:phone:" . hash('md5', $phoneNumberObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($phoneNumberObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('paymentMethods', function ($query) use ($phoneNumberObj) {
                $query->where('reference', '=', $phoneNumberObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }

    private function findScammerByUrl(string $url, int $page, int $count, array $relationships): Collection
    {
        $urlObj = new URL($url);
        $cacheKey = "scammers:profile:url:" . hash('md5', $urlObj) . ":$page:$count:" . implode(',', $relationships);

        return Cache::remember($cacheKey, 3600, function () use ($urlObj, $page, $count, $relationships) {
            $query = Scammer::whereHas('profiles', function ($query) use ($urlObj) {
                $query->where('contact', '=', $urlObj);
            });

            $eagerLoads = $this->getEagerLoads($relationships);
            if (!empty($eagerLoads)) {
                $query->with($eagerLoads);
            }

            $scammers = $query->paginate($count, ['id', 'name', 'iso_country', 'is_active'], 'page', $page);

            return $scammers->getCollection()->each(function ($scammer) {
                if ($scammer->relationLoaded('organizations')) {
                    $scammer->organizations->makeHidden('pivot');
                }
            });
        });
    }
}
