<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\RegexMatcher;

class ParameterMatcherTest extends TestCase
{
    public function testMatcher(): void
    {
        foreach ([
            new ParameterMatcher('param', Pattern::ANY),
            new ParameterMatcher('param', Pattern::ALPHA_NUM),
        ] as $segment) {
            $parameters = [];

            self::assertInstanceOf(RegexMatcher::class, $segment->getMatcher($parameters));
            self::assertSame([0 => 'param'], $parameters);
        }
    }
}
