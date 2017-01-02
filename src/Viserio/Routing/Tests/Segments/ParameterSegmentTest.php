<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Segments;

use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Matchers\RegexMatcher;
use Viserio\Routing\Segments\ParameterSegment;
use PHPUnit\Framework\TestCase;

class ParameterSegmentTest extends TestCase
{
    public function testMatcher()
    {
        foreach ([
            new ParameterSegment('param', Pattern::ANY),
            new ParameterSegment('param', Pattern::ALPHA_NUM),
        ] as $segment) {
            $parameters = [];

            self::assertInstanceOf(RegexMatcher::class, $segment->getMatcher($parameters));
            self::assertSame([0 => 'param'], $parameters);
        }
    }
}
