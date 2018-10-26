<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\AnyMatcher;
use Viserio\Component\Routing\Matcher\CompoundMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;

/**
 * @internal
 */
final class CompoundMatcherTest extends TestCase
{
    public function testGetConditionExpression(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        $this->assertSame('test === \'test\' && test !== \'\'', $matcher->getConditionExpression('test', 2));
    }

    public function testGetMatchedParameterExpressions(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        $this->assertSame([1 => 'test', 0 => 'test'], $matcher->getMatchedParameterExpressions('test', 2));
    }

    public function testGetHash(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        $this->assertSame('Viserio\Component\Routing\Matcher\CompoundMatcher:Viserio\Component\Routing\Matcher\StaticMatcher:test::Viserio\Component\Routing\Matcher\AnyMatcher:', $matcher->getHash());
    }

    public function testCompoundSegmentMatcher(): void
    {
        $matcher1 = new CompoundMatcher([new StaticMatcher('a'), new StaticMatcher('b', [0])]);
        $matcher2 = new CompoundMatcher([new StaticMatcher('a', [0]), new StaticMatcher('c', [1])]);

        $this->assertSame([0], $matcher1->getParameterKeys());
        $this->assertNotEquals($matcher2->getHash(), $matcher1->getHash());
        $this->assertSame('$segment === \'a\' && $segment === \'b\'', $matcher1->getConditionExpression('$segment', 0));
        $this->assertSame([0 => '$segment'], $matcher1->getMatchedParameterExpressions('$segment', 0));
        $this->assertSame('$segment === \'a\' && $segment === \'c\'', $matcher2->getConditionExpression('$segment', 0));
        $this->assertSame([0 => '$segment', 1 => '$segment'], $matcher2->getMatchedParameterExpressions('$segment', 0));
    }
}
