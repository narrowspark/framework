<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\StaticMatcher;

/**
 * @internal
 */
final class StaticMatcherTest extends TestCase
{
    public function testCannotContainSlash(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create Viserio\\Component\\Routing\\Matcher\\StaticMatcher: segment cannot contain \'/\', \'abc/foo\' given.');

        new StaticMatcher('abc/foo');
    }

    public function testGetConditionExpression(): void
    {
        $matcher = new StaticMatcher('one');

        $this->assertSame('one === \'one\'', $matcher->getConditionExpression('one'));
    }

    public function testGetMatchedParameterExpressions(): void
    {
        $matcher = new StaticMatcher('two', [1]);

        $this->assertSame([1 => 'two'], $matcher->getMatchedParameterExpressions('two'));

        $matcher = new StaticMatcher('three');

        $this->assertSame([], $matcher->getMatchedParameterExpressions('three'));
    }

    public function testMergeParameterKeys(): void
    {
        $matcher  = new StaticMatcher('two', [2]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);

        $this->assertSame([2 => 'two'], $matcher->getMatchedParameterExpressions('two'));
    }
}
