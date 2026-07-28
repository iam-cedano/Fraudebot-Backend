<?php
namespace App\Repositories\Search;

use Illuminate\Support\Collection;
interface ClueSearchInterface
{
    public function findByName(string $name, int $page, int $count): Collection;
    public function findByCardNumber(string $cardNumber, int $page, int $count): Collection;
    public function findByClabe(string $clabe, int $page, int $count): Collection;
    public function findByAccountNumber(string $accountNumber, int $page, int $count): Collection;
    public function findByEmail(string $email, int $page, int $count): Collection;
    public function findByPhoneNumber(string $phoneNumber, int $page, int $count): Collection;
    public function findByUrl(string $url, int $page, int $count): Collection;
}