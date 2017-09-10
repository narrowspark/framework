<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\StaticMatcher;

class StaticMatcherTest extends TestCase
{
    /**
     * @expectedException \Viserio\Component\Contract\Routing\Exception\InvalidArgumentException
     * @expectedExceptionMessage Cannot create Viserio\Component\Routing\Matcher\StaticMatcher: segment cannot contain '/', 'abc/foo' given.
     */
    public function testCannotContainSlash(): void
    {
        new StaticMatcher('abc/foo');
    }

    public function testGetConditionExpression(): void
    {
        $matcher = new StaticMatcher('one');

        self::assertSame('one === \'one\'', $matcher->getConditionExpression('one'));
    }

    public function testGetMatchedParameterExpressions(): void
    {
        $matcher = new StaticMatcher('two', [1]);

        self::assertSame([1 => 'two'], $matcher->getMatchedParameterExpressions('two'));

        $matcher = new StaticMatcher('three');

        self::assertSame([], $matcher->getMatchedParameterExpressions('three'));
    }

    public function testMergeParameterKeys(): void
    {
        $matcher  = new StaticMatcher('two', [2]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);

        self::assertSame([2 => 'two'], $matcher->getMatchedParameterExpressions('two'));
    }
}
