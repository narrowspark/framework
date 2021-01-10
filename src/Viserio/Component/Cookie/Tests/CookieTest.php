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

namespace Viserio\Component\Cookie\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\Cookie;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CookieTest extends TestCase
{
    public static function provideInstantiationThrowsExceptionIfCookieNameContainsInvalidCharactersCases(): iterable
    {
        return [
            [',MyName'],
            [';MyName'],
            [' MyName'],
            ["\tMyName"],
            ["\rMyName"],
            ["\nMyName"],
            ["\013MyName"],
            ["\014MyName"],
        ];
    }

    /**
     * @expectExceptionMessage The name cannot be empty.
     */
    public function testInstantiationThrowsExceptionIfCookieNameIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie('');
    }

    /**
     * @dataProvider provideInstantiationThrowsExceptionIfCookieNameContainsInvalidCharactersCases
     */
    public function testInstantiationThrowsExceptionIfCookieNameContainsInvalidCharacters($name): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Cookie name [' . $name . '] must not contain invalid characters: ASCII Control characters (0-31;127), space, tab and the following characters: ()<>@,;:\"/[]?={}');

        new Cookie($name);
    }

    public static function provideInstantiationThrowsExceptionIfCookieValueContainsInvalidCharactersCases(): iterable
    {
        return [
            [',Value'],
            [';Value'],
            [' Value'],
            ["\tValue"],
            ["\rValue"],
            ["\nValue"],
            ["\013Value"],
            ["\014Value"],
        ];
    }

    /**
     * @dataProvider provideInstantiationThrowsExceptionIfCookieValueContainsInvalidCharactersCases
     */
    public function testInstantiationThrowsExceptionIfCookieValueContainsInvalidCharacters($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie('MyCookie', $value);
    }

    public function testGetValue(): void
    {
        $value = 'MyValue';
        $cookie = new Cookie('MyCookie', $value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testWithValue(): void
    {
        $value = 'MyValue';
        $cookie = new Cookie('MyCookie');
        $cookie = $cookie->withValue($value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testToString(): void
    {
        $cookie = new Cookie('MyCookie', 'MyValue');

        self::assertSame('MyCookie=MyValue', (string) $cookie);
    }

    public function testGetName(): void
    {
        $cookie = new Cookie($name = 'MyCookie');

        self::assertSame($name, $cookie->getName());
    }
}
