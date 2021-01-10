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

namespace Viserio\Component\Finder\Tests\Comparator;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Comparator\Comparator;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ComparatorTest extends TestCase
{
    public function testGetSetOperatorToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $comparator = new Comparator();
        $comparator->setOperator('foo');
    }

    public function testGetSetOperator(): void
    {
        $comparator = new Comparator();
        $comparator->setOperator('>');

        self::assertEquals('>', $comparator->getOperator(), '->getOperator() returns the current operator');
    }

    public function testGetSetTarget(): void
    {
        $comparator = new Comparator();
        $comparator->setTarget(8);

        self::assertEquals(8, $comparator->getTarget(), '->getTarget() returns the target');
    }

    /**
     * @dataProvider provideTestCases
     *
     * @param string[] $match
     * @param string[] $noMatch
     */
    public function testTest(string $operator, string $target, array $match, array $noMatch): void
    {
        $c = new Comparator();
        $c->setOperator($operator);
        $c->setTarget($target);

        foreach ($match as $m) {
            self::assertTrue($c->test($m), '->test() tests a string against the expression');
        }

        foreach ($noMatch as $m) {
            self::assertFalse($c->test($m), '->test() tests a string against the expression');
        }
    }

    /**
     * @return iterable<array<int, array<int, string>|string>>
     */
    public static function provideTestCases(): iterable
    {
        yield ['<', '1000', ['500', '999'], ['1000', '1500']];
    }
}
