<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\ExpressionMatcher;
use Viserio\Routing\Matchers\StaticMatcher;

use PHPUnit\Framework\TestCase;
class ExpressionMatcherTest extends TestCase
{
    public function testGetExpression()
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        self::assertSame('ctype_digit({segment})', $matcher->getExpression());
    }

    public function testGetConditionExpression()
    {
        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);

        self::assertSame('ctype_digit(test)', $matcher->getConditionExpression('test'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot merge parameters: matchers must be equivalent, 'Viserio\Routing\Matchers\StaticMatcher:two' expected, 'Viserio\Routing\Matchers\ExpressionMatcher:ctype_digit({segment})' given.
     */
    public function testMergeParameterKeys()
    {
        $matcher  = new ExpressionMatcher('ctype_digit({segment})', [1]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);
    }
}
