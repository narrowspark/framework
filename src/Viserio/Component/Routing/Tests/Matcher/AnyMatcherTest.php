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

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class AnyMatcherTest extends TestCase
{
    public function testGetConditionExpression(): void
    {
        $matcher = new AnyMatcher([0]);

        self::assertSame('segment/[test] !== \'\'', $matcher->getConditionExpression('segment/[test]'));
    }

    public function testAnyMergingParameterKeys(): void
    {
        $matcher1 = new AnyMatcher([123]);
        $matcher2 = new AnyMatcher([12, 3]);
        $matcher1->mergeParameterKeys($matcher2);

        self::assertSame([123, 12, 3], $matcher1->getParameterKeys());
    }
}
