<?php

namespace App\Repositories\Search;

use Illuminate\Database\Eloquent\Builder;

interface ClueSearchInterface
{
    public function matchByName(string $name): ?Builder;

    public function matchByCardNumber(string $cardNumber): ?Builder;

    public function matchByClabe(string $clabe): ?Builder;

    public function matchByAccountNumber(string $accountNumber): ?Builder;

    public function matchByEmail(string $email): ?Builder;

    public function matchByPhoneNumber(string $phoneNumber): ?Builder;

    public function matchByUrl(string $url): ?Builder;

    public function matchByWallet(string $wallet): ?Builder;
}
