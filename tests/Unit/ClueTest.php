<?php

namespace Tests\Unit;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Domain\Scammer\Enums\ClueType;
use PHPUnit\Framework\TestCase;

class ClueTest extends TestCase
{
    public function testClueByCardNumber(): void
    {
        $clue = new Clue('4152313732125521');

        $this->assertEquals(ClueType::CardNumber, $clue->getType());
    }

    public function testClueByAccountNumber(): void
    {
        $clue = new Clue('1234567890');

        $this->assertEquals(ClueType::AccountNumber, $clue->getType());
    }

    public function testClueByEmail(): void
    {
        $clue = new Clue('test@example.com');

        $this->assertEquals(ClueType::Email, $clue->getType());
    }

    public function testClueByPhone(): void
    {
        $clue = new Clue('521234567890');

        $this->assertEquals(ClueType::Phone, $clue->getType());
    }


    public function testClueByUrl(): void
    {
        $clue = new Clue('https://example.com');

        $this->assertEquals(ClueType::Url, $clue->getType());
    }

    public function testClueByIpAddress(): void
    {
        $clue = new Clue('127.0.0.1');

        $this->assertEquals(ClueType::IpAddress, $clue->getType());
    }
}