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
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\RegexMatcher;
use Viserio\Contract\Routing\Pattern;

/**
 * @internal
 *
 * @small
 */
final class ParameterMatcherTest extends TestCase
{
    public function testMatcher(): void
    {
        foreach ([
            new ParameterMatcher('param', Pattern::ANY),
            new ParameterMatcher('param', Pattern::ALPHA_NUM),
        ] as $segment) {
            $parameters = [];

            self::assertInstanceOf(RegexMatcher::class, $segment->getMatcher($parameters));
            self::assertSame([0 => 'param'], $parameters);
        }
    }
}
