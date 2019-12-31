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

namespace Viserio\Component\Finder\Tests\Comparator;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Comparator\Comparator;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
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
     * @param string   $operator
     * @param string   $target
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
    public function provideTestCases(): iterable
    {
        yield ['<', '1000', ['500', '999'], ['1000', '1500']];
    }
}
