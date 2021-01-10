<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests\Matchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\AnyMatcher;
use Viserio\Component\Routing\Matcher\CompoundMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CompoundMatcherTest extends TestCase
{
    public function testGetConditionExpression(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame('test === \'test\' && test !== \'\'', $matcher->getConditionExpression('test', 2));
    }

    public function testGetMatchedParameterExpressions(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame([1 => 'test', 0 => 'test'], $matcher->getMatchedParameterExpressions('test', 2));
    }

    public function testGetHash(): void
    {
        $matcher = new CompoundMatcher([
            new StaticMatcher('test', [1]),
            new AnyMatcher([0]),
        ]);

        self::assertSame('Viserio\Component\Routing\Matcher\CompoundMatcher:Viserio\Component\Routing\Matcher\StaticMatcher:test::Viserio\Component\Routing\Matcher\AnyMatcher:', $matcher->getHash());
    }

    public function testCompoundSegmentMatcher(): void
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
