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
use Viserio\Component\Finder\Comparator\NumberComparator;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class NumberComparatorTest extends TestCase
{
    /**
     * @dataProvider provideConstructorCases
     *
     * @param string $test
     * @param bool   $throw
     * @param bool   $isNumber
     *
     * @return void
     */
    public function testConstructor(string $test, bool $throw = false, bool $isNumber = false): void
    {
        if ($throw) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessageRegExp(! $isNumber ? '/Don\'t understand \[.*\] as a number test\./' : '/Invalid number \[.*\]\./');
        }

        $comparator = new NumberComparator($test);

        if (! $throw) {
            self::assertNotSame('', $comparator->getTarget());
        }
    }

    /**
     * @dataProvider provideTestCases
     *
     * @param string            $test
     * @param array<int|string> $match
     * @param array<int|string> $noMatch
     *
     * @return void
     */
    public function testTest(string $test, array $match, array $noMatch): void
    {
        $c = new NumberComparator($test);

        foreach ($match as $m) {
            self::assertTrue($c->test($m), '->test() tests a string against the expression');
        }

        foreach ($noMatch as $m) {
            self::assertFalse($c->test($m), '->test() tests a string against the expression');
        }
    }

    /**
     * @return iterable<array<int, array<int, int|string>|string>>
     */
    public function provideTestCases(): iterable
    {
        yield ['< 1000', ['500', '999'], ['1000', '1500']];

        yield ['< 1K', ['500', '999'], ['1000', '1500']];

        yield ['<1k', ['500', '999'], ['1000', '1500']];

        yield ['  < 1 K ', ['500', '999'], ['1000', '1500']];

        yield ['<= 1K', ['1000'], ['1001']];

        yield ['> 1K', ['1001'], ['1000']];

        yield ['>= 1K', ['1000'], ['999']];

        yield ['< 1KI', ['500', '1023'], ['1024', '1500']];

        yield ['<= 1KI', ['1024'], ['1025']];

        yield ['> 1KI', ['1025'], ['1024']];

        yield ['>= 1KI', ['1024'], ['1023']];

        yield ['1KI', ['1024'], ['1023', '1025']];

        yield ['==1KI', ['1024'], ['1023', '1025']];

        yield ['==1m', ['1000000'], ['999999', '1000001']];

        yield ['==1mi', [1024 * 1024], [1024 * 1024 - 1, 1024 * 1024 + 1]];

        yield ['==1g', ['1000000000'], ['999999999', '1000000001']];

        yield ['==1gi', [1024 * 1024 * 1024], [1024 * 1024 * 1024 - 1, 1024 * 1024 * 1024 + 1]];

        yield ['!= 1000', ['500', '999'], ['1000']];
    }

    /**
     * @return iterable<array<int, bool|string>>
     */
    public function provideConstructorCases(): iterable
    {
        yield ['1'];

        yield ['0'];

        yield ['3.5'];

        yield ['33.55'];

        yield ['123.456'];

        yield ['123456.78'];

        yield ['.1'];

        yield ['.123'];

        yield ['.0'];

        yield ['0.0'];

        yield ['1.'];

        yield ['0.'];

        yield ['123.'];

        yield ['==1'];

        yield ['!=1'];

        yield ['<1'];

        yield ['>1'];

        yield ['<=1'];

        yield ['>=1'];

        yield ['==1k'];

        yield ['==1ki'];

        yield ['==1m'];

        yield ['==1mi'];

        yield ['==1g'];

        yield ['==1gi'];

        yield ['1k'];

        yield ['1ki'];

        yield ['1m'];

        yield ['1mi'];

        yield ['1g'];

        yield ['1gi'];

        // throws
        yield [' ', true];

        yield ['foobar', true];

        yield ['=1', true];

        yield ['===1', true];

        yield ['0 . 1', true];

        yield ['123 .45', true];

        yield ['234. 567', true];

        yield ['..', true, true];

        yield ['.0.', true, true];

        yield ['0.1.2', true, true];
    }
}
