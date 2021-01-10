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
use Viserio\Component\Routing\Matcher\ExpressionMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Contract\Routing\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ExpressionMatcherTest extends TestCase
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

    public function testMergeParameterKeysWithTwoDifferentMatcher(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge parameters: Matcher\'s must be equivalent, [Viserio\\Component\\Routing\\Matcher\\StaticMatcher:two] expected, [Viserio\\Component\\Routing\\Matcher\\ExpressionMatcher:ctype_digit({segment})] given.');

        $matcher = new ExpressionMatcher('ctype_digit({segment})', [1]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);
    }
}
