<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Segments;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Routing\Pattern;
use Viserio\Component\Routing\Matchers\RegexMatcher;
use Viserio\Component\Routing\Segments\ParameterSegment;

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
