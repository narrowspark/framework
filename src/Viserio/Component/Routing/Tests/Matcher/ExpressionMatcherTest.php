<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
