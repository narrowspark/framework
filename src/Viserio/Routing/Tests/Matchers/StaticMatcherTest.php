<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\StaticMatcher;
use PHPUnit\Framework\TestCase;

class StaticMatcherTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testCannotContainSlash()
    {
        new StaticMatcher('abc/foo');
    }

    public function testGetConditionExpression()
    {
        $matcher = new StaticMatcher('one');

        self::assertSame('one === \'one\'', $matcher->getConditionExpression('one'));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new StaticMatcher('two', [1]);

        self::assertSame([1 => 'two'], $matcher->getMatchedParameterExpressions('two'));

        $matcher = new StaticMatcher('three');

        self::assertSame([], $matcher->getMatchedParameterExpressions('three'));
    }

    public function testMergeParameterKeys()
    {
        $matcher  = new StaticMatcher('two', [2]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);

        self::assertSame([2 => 'two'], $matcher->getMatchedParameterExpressions('two'));
    }
}
