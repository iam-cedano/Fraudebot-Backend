<?php

namespace Tests\Unit;

use App\Domain\Scammer\ScammerEntity;
use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Controllers\Public\ScammerController;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScammerController::class)]
final class PublicScammerControllerTest extends TestCase
{
    public function testScammerSearchByCardNumber(): void
    {
        $queryParam = '5555555555555444';

        $scammerRepositoryMock = $this->createMock(ScammerRepositoryInterface::class);

        $scammerRepositoryMock->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(fn(Clue $clue) => $clue->getValue() === $queryParam),
                1,
                10,
                []
            )
            ->willReturn(collect([]));

        $controller = new ScammerController($scammerRepositoryMock);

        $request = Request::create('/api/scammers', 'GET', ['q' => $queryParam]);

        $response = $controller->index($request);

        $this->assertNotNull($response);
    }

    public function testScammerSearchByClabe(): void
    {
        $queryParam = '012345678901234567';

        $scammerRepositoryMock = $this->createMock(ScammerRepositoryInterface::class);

        $scammerRepositoryMock->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(fn(Clue $clue) => $clue->getValue() === $queryParam),
                1,
                10,
                []
            )
            ->willReturn(collect([]));

        $controller = new ScammerController($scammerRepositoryMock);

        $request = Request::create('/api/scammers', 'GET', ['q' => $queryParam]);

        $response = $controller->index($request);

        $this->assertNotNull($response);
    }
}