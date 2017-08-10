<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\ExpressionMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;

class ExpressionMatcherTest extends TestCase
{
    public function testGetExpression(): void
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        self::assertSame('ctype_digit({segment})', $matcher->getExpression());
    }

    public function testGetConditionExpression(): void
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        self::assertSame('ctype_digit(test)', $matcher->getConditionExpression('test'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exception\InvalidArgumentException
     * @expectedExceptionMessage Cannot merge parameters: Matcher's must be equivalent, [Viserio\Component\Routing\Matcher\StaticMatcher:two] expected, [Viserio\Component\Routing\Matcher\ExpressionMatcher:ctype_digit({segment})] given.
     */
    public function testMergeParameterKeysWithTwoDifferentMatcher(): void
    {
        $matcher  = new ExpressionMatcher('ctype_digit({segment})', [1]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);
    }
}
