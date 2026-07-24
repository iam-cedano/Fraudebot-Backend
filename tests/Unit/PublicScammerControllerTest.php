<?php

namespace Tests\Unit;

use App\Domain\Scammer\ValueObjects\Clue;
use App\Http\Controllers\Public\ScammerController;
use App\Models\Scammer;
use App\Repositories\Scammer\ScammerRepositoryInterface;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScammerController::class)]
final class PublicScammerControllerTest extends TestCase
{
    public function testScammerSearchByCardNumber(): void
    {
        $queryParam = '4152313732125521';

        $scammer = new Scammer();
        $scammer->forceFill([
            'id' => 1,
            'name' => 'Ariel Hugo Dominguez',
            'iso_country' => 'MX',
            'is_active' => true,
        ]);

        $scammerRepositoryMock = $this->createMock(ScammerRepositoryInterface::class);

        $scammerRepositoryMock->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(fn (Clue $clue) => $clue->getValue() === $queryParam),
                1,
                10,
                []
            )
            ->willReturn(collect([$scammer]));

        $controller = new ScammerController($scammerRepositoryMock);

        $request = Request::create('/api/scammers', 'GET', ['q' => $queryParam]);

        $response = $controller->index($request);

        $this->assertEquals(1, $response->count());
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'Ariel Hugo Dominguez',
                'iso_country' => 'MX',
                'is_active' => true,
            ],
        ], $response->resolve($request));
    }

    public function testScammerSearchByClabe(): void
    {
        $queryParam = '012345678901234567';

        $scammerRepositoryMock = $this->createMock(ScammerRepositoryInterface::class);

        $scammerRepositoryMock->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(fn (Clue $clue) => $clue->getValue() === $queryParam),
                1,
                10,
                []
            )
            ->willReturn(collect([]));

        $controller = new ScammerController($scammerRepositoryMock);

        $request = Request::create('/api/scammers', 'GET', ['q' => $queryParam]);

        $response = $controller->index($request);

        $this->assertEquals(0, $response->count());
        $this->assertSame([], $response->resolve($request));
    }
}
