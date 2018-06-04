<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\Cookie;

/**
 * @internal
 */
final class CookieTest extends TestCase
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
     * @expectExceptionMessage The name cannot be empty.
     */
    public function testInstantiationThrowsExceptionIfCookieNameIsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

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
     *
     * @param mixed $value
     */
    public function testInstantiationThrowsExceptionIfCookieValueContainsInvalidCharacters($value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Cookie('MyCookie', $value);
    }

    public function testGetValue(): void
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie', $value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testWithValue(): void
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie');
        $cookie = $cookie->withValue($value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testToString(): void
    {
        $cookie = new Cookie('MyCookie', 'MyValue');

        $this->assertSame('MyCookie=MyValue', (string) $cookie);
    }
}
