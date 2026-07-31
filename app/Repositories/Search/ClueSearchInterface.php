<?php
namespace App\Repositories\Search;

use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the WHERE-clause-only query for a given clue value, scoped to a
 * single source's Eloquent model. Returning null means "this clue type is
 * not (yet) searchable for this source" and it is excluded from the search
 * entirely (as opposed to a query that matches zero rows).
 */
interface ClueSearchInterface
{
    public function matchByName(string $name): ?Builder;
    public function matchByCardNumber(string $cardNumber): ?Builder;
    public function matchByClabe(string $clabe): ?Builder;
    public function matchByAccountNumber(string $accountNumber): ?Builder;
    public function matchByEmail(string $email): ?Builder;
    public function matchByPhoneNumber(string $phoneNumber): ?Builder;
    public function matchByUrl(string $url): ?Builder;
    public function matchByIpAddress(string $ipAddress): ?Builder;
    public function matchByUsername(string $username): ?Builder;
}
