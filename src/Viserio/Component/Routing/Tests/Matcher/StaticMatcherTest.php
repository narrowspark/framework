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
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Contract\Routing\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class StaticMatcherTest extends TestCase
{
    public function testCannotContainSlash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create Viserio\\Component\\Routing\\Matcher\\StaticMatcher: segment cannot contain \'/\', \'abc/foo\' given.');

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
        $matcher = new StaticMatcher('two', [2]);
        $matcher2 = new StaticMatcher('two', [3]);
        $matcher->mergeParameterKeys($matcher2);

        self::assertSame([2 => 'two'], $matcher->getMatchedParameterExpressions('two'));
    }
}
