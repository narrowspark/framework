<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Segments;

use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Matchers\RegexMatcher;
use Viserio\Routing\Segments\ParameterSegment;

class ParameterSegmentTest extends \PHPUnit_Framework_TestCase
{
    public function testMatcher()
    {
        foreach ([
            new ParameterSegment('param', Pattern::ANY),
            new ParameterSegment('param', Pattern::ALPHA_NUM),
        ] as $segment) {
            $parameters = [];

            $this->assertInstanceOf(RegexMatcher::class, $segment->getMatcher($parameters));
            $this->assertSame([0 => 'param'], $parameters);
        }
    }
}
