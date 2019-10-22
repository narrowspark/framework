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
use Viserio\Component\Routing\Matcher\AnyMatcher;

/**
 * @internal
 *
 * @small
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
