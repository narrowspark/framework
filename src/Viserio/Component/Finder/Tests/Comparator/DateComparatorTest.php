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

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Comparator\DateComparator;

/**
 * @internal
 *
 * @small
 */
final class DateComparatorTest extends TestCase
{
    public function testConstructor(): void
    {
        try {
            new DateComparator('foobar');
            self::fail('__construct() throws an \InvalidArgumentException if the test expression is not valid.');
        } catch (Exception $e) {
            self::assertInstanceOf('InvalidArgumentException', $e, '__construct() throws an \InvalidArgumentException if the test expression is not valid.');
        }

        try {
            new DateComparator('');
            self::fail('__construct() throws an \InvalidArgumentException if the test expression is not valid.');
        } catch (Exception $e) {
            self::assertInstanceOf('InvalidArgumentException', $e, '__construct() throws an \InvalidArgumentException if the test expression is not valid.');
        }
    }

    /**
     * @dataProvider provideTestCases
     *
     * @param mixed $test
     * @param mixed $match
     * @param mixed $noMatch
     */
    public function testTest($test, $match, $noMatch): void
    {
        $c = new DateComparator($test);

        foreach ($match as $m) {
            self::assertTrue($c->test($m), '->test() tests a string against the expression');
        }

        foreach ($noMatch as $m) {
            self::assertFalse($c->test($m), '->test() tests a string against the expression');
        }
    }

    public function provideTestCases(): iterable
    {
        return [
            ['< 2005-10-10', [\strtotime('2005-10-09')], [\strtotime('2005-10-15')]],
            ['until 2005-10-10', [\strtotime('2005-10-09')], [\strtotime('2005-10-15')]],
            ['before 2005-10-10', [\strtotime('2005-10-09')], [\strtotime('2005-10-15')]],
            ['> 2005-10-10', [\strtotime('2005-10-15')], [\strtotime('2005-10-09')]],
            ['after 2005-10-10', [\strtotime('2005-10-15')], [\strtotime('2005-10-09')]],
            ['since 2005-10-10', [\strtotime('2005-10-15')], [\strtotime('2005-10-09')]],
            ['!= 2005-10-10', [\strtotime('2005-10-11')], [\strtotime('2005-10-10')]],
        ];
    }
}
