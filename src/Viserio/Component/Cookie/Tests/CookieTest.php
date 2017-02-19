<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\Cookie;

class CookieTest extends TestCase
{
    public function invalidNames()
    {
        return [
            [''],
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
     * @dataProvider invalidNames
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $name
     */
    public function testInstantiationThrowsExceptionIfCookieNameContainsInvalidCharacters($name)
    {
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
    public function testInstantiationThrowsExceptionIfCookieValueContainsInvalidCharacters($value)
    {
        $cookie = new Cookie('MyCookie', $value);
    }

    public function testGetValue()
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie', $value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testWithValue()
    {
        $value  = 'MyValue';
        $cookie = new Cookie('MyCookie');
        $cookie = $cookie->withValue($value);

        self::assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testToString()
    {
        $cookie = new Cookie('MyCookie', 'MyValue');

        self::assertSame('MyCookie=MyValue', (string) $cookie);
    }
}
