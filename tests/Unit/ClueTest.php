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

        $this->assertEquals(ClueType::CARD_NUMBER, $clue->getType());
    }

    public function test_clue_by_account_number(): void
    {
        $clue = new Clue('1234567890');

        $this->assertEquals(ClueType::ACCOUNT_NUMBER, $clue->getType());
    }

    public function test_clue_by_email(): void
    {
        $clue = new Clue('test@example.com');

        $this->assertEquals(ClueType::EMAIL, $clue->getType());
    }

    public function test_clue_by_phone(): void
    {
        $clue = new Clue('521234567890');

        $this->assertEquals(ClueType::PHONE, $clue->getType());
    }

    public function test_clue_by_url(): void
    {
        $clue = new Clue('https://example.com');

        $this->assertEquals(ClueType::URL, $clue->getType());
    }

    public function test_clue_by_bitcoin_wallet(): void
    {
        $clue = new Clue('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa');

        $this->assertEquals(ClueType::WALLET, $clue->getType());
    }

    public function test_clue_by_ethereum_wallet(): void
    {
        $clue = new Clue('0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb0');

        $this->assertEquals(ClueType::WALLET, $clue->getType());
    }

    public function test_ip_address_is_not_a_supported_clue(): void
    {
        $clue = new Clue('127.0.0.1');

        $this->assertEquals(ClueType::NOTHING, $clue->getType());
    }
}
