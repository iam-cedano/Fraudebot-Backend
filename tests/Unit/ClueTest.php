<?php

namespace Tests\Unit;

use App\Domain\Scammer\Enums\ClueType;
use App\Domain\Scammer\ValueObjects\Clue;
use PHPUnit\Framework\TestCase;

class ClueTest extends TestCase
{
    public function test_clue_by_card_number(): void
    {
        $clue = new Clue('4152313732125521');

        $this->assertEquals(ClueType::CardNumber, $clue->getType());
    }

    public function test_clue_by_account_number(): void
    {
        $clue = new Clue('1234567890');

        $this->assertEquals(ClueType::AccountNumber, $clue->getType());
    }

    public function test_clue_by_email(): void
    {
        $clue = new Clue('test@example.com');

        $this->assertEquals(ClueType::Email, $clue->getType());
    }

    public function test_clue_by_phone(): void
    {
        $clue = new Clue('521234567890');

        $this->assertEquals(ClueType::Phone, $clue->getType());
    }

    public function test_clue_by_url(): void
    {
        $clue = new Clue('https://example.com');

        $this->assertEquals(ClueType::Url, $clue->getType());
    }

    public function test_ip_address_is_not_a_supported_clue(): void
    {
        $clue = new Clue('127.0.0.1');

        $this->assertEquals(ClueType::Nothing, $clue->getType());
    }
}
