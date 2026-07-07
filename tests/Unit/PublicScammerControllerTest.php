<?php

namespace Tests\Unit;

use App\Http\Controllers\Public\ScammerController;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScammerController::class)]
final class PublicScammerControllerTest extends TestCase
{

    public function testScammerSearchByCardNumber(): void
    {
        $this->assertTrue(true);
    }
}
