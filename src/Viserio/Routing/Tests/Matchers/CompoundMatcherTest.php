<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Matchers;

use Viserio\Routing\Matchers\AnyMatcher;
use Viserio\Routing\Matchers\CompoundMatcher;
use Viserio\Routing\Matchers\StaticMatcher;

class CompoundMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConditionExpression()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame('test === \'test\' && test !== \'\'', $matcher->getConditionExpression('test', 2));
    }

    public function testGetMatchedParameterExpressions()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame([1 => 'test', 0 => 'test'], $matcher->getMatchedParameterExpressions('test', 2));
    }

    public function testGetHash()
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame('Viserio\Routing\Matchers\CompoundMatcher:Viserio\Routing\Matchers\StaticMatcher:test::Viserio\Routing\Matchers\AnyMatcher:', $matcher->getHash());
    }

    public function testCompoundSegmentMatcher()
    {
        $matcher1 = new CompoundMatcher([new StaticMatcher('a'), new StaticMatcher('b', [0])]);
        $matcher2 = new CompoundMatcher([new StaticMatcher('a', [0]), new StaticMatcher('c', [1])]);

        self::assertSame([0], $matcher1->getParameterKeys());
        self::assertNotEquals($matcher2->getHash(), $matcher1->getHash());
        self::assertSame('$segment === \'a\' && $segment === \'b\'', $matcher1->getConditionExpression('$segment', 0));
        self::assertSame([0 => '$segment'], $matcher1->getMatchedParameterExpressions('$segment', 0));
        self::assertSame('$segment === \'a\' && $segment === \'c\'', $matcher2->getConditionExpression('$segment', 0));
        self::assertSame([0 => '$segment', 1 => '$segment'], $matcher2->getMatchedParameterExpressions('$segment', 0));
    }
}
