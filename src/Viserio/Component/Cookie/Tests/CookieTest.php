<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\Cookie;

class CookieTest extends TestCase
{
    public function invalidNames()
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
     * @expectedException \InvalidArgumentException
     * @expectExceptionMessage The name cannot be empty.
     */
    public function testInstantiationThrowsExceptionIfCookieNameIsEmpty(): void
    {
        new Cookie('');
    }

    /**
     * @dataProvider invalidNames
     *
     * @param mixed $name
     */
    public function testInstantiationThrowsExceptionIfCookieNameContainsInvalidCharacters($name): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Cookie name [' . $name . '] must not contain invalid characters: ASCII Control characters (0-31;127), space, tab and the following characters: ()<>@,;:\"/[]?={}');

        new Cookie($name);
    }

    public function invalidValues()
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
     * @dataProvider invalidValues
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $value
     */
    public function testInstantiationThrowsExceptionIfCookieValueContainsInvalidCharacters($value): void
    {
        $cookie = new Cookie('MyCookie', $value);
    }

    public function testGetValue(): void
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie', $value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testWithValue(): void
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie');
        $cookie = $cookie->withValue($value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testToString(): void
    {
        $cookie = new Cookie('MyCookie', 'MyValue');

        self::assertSame('MyCookie=MyValue', (string) $cookie);
    }
}
