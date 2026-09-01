<?php

namespace Tests\Unit;

use App\Domain\Search\CardPreview;
use PHPUnit\Framework\TestCase;

class CardPreviewTest extends TestCase
{
    public function test_names_appends_ellipsis_after_the_limit(): void
    {
        $this->assertSame(
            ['a', 'b', 'c', 'd', 'e', '...'],
            CardPreview::names(['c', 'a', 'b', 'f', 'e', 'd']),
        );
    }

    public function test_names_does_not_truncate_at_the_limit(): void
    {
        $this->assertSame(
            ['a', 'b', 'c', 'd', 'e'],
            CardPreview::names(['e', 'd', 'c', 'b', 'a']),
        );
    }

    public function test_names_can_unique_before_limiting(): void
    {
        $this->assertSame(
            ['a', 'b'],
            CardPreview::names(['b', 'a', 'a', 'b'], unique: true),
        );
    }
}
