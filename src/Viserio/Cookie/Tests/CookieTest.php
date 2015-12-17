<?php
namespace Viserio\Cookie\Test;

use DateTime;
use Viserio\Cookie\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function invalidNames()
    {
        return array(
            array(''),
            array(',MyName'),
            array(';MyName'),
            array(' MyName'),
            array("\tMyName"),
            array("\rMyName"),
            array("\nMyName"),
            array("\013MyName"),
            array("\014MyName"),
        );
    }

    /**
     * @dataProvider invalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiationThrowsExceptionIfCookieNameContainsInvalidCharacters($name)
    {
        new Cookie($name);
    }

    public function invalidValues()
    {
        return array(
            array(',Value'),
            array(';Value'),
            array(' Value'),
            array("\tValue"),
            array("\rValue"),
            array("\nValue"),
            array("\013Value"),
            array("\014Value"),
        );
    }

    /**
     * @dataProvider invalidValues
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiationThrowsExceptionIfCookieValueContainsInvalidCharacters($value)
    {
        $cookie = new Cookie('MyCookie', $value);
    }

    public function testGetValue()
    {
        $value = 'MyValue';
        $cookie = new Cookie('MyCookie', $value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
        $this->assertTrue($cookie->hasValue(), '->hasValue() returns true if the value exist');
    }

    public function testWithValue()
    {
        $value = 'MyValue';
        $cookie = new Cookie('MyCookie');
        $cookie = $cookie->withValue($value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testGetPatchAndWithPath()
    {
        $cookie = new Cookie('foo', 'bar');

        $this->assertSame('/', $cookie->getPath(), '->getPath() returns / as the default path');

        $cookie = $cookie->withPath('/tests/');

        $this->assertSame('/tests', $cookie->getPath(), '->getPath() returns / as the default path');
    }

    public function testMatchPath()
    {
        $cookie = new Cookie('foo', 'bar', '/');

        $this->assertTrue($cookie->matchPath('/'), '->matchPath() returns true if the paths match');
        $this->assertFalse($cookie->matchPath('/path/to/somewhere'), '->matchPath() returns false if the paths not match');
    }

    public function testMatchCookie()
    {
        $cookie = new Cookie('foo', 'bar', '/');
        $cookie2 = new Cookie('bar', 'foo', '/');

        $this->assertTrue($cookie->matchCookie($cookie), '->matchCookie() returns true if both cookies are identical');
        $this->assertFalse($cookie->matchCookie($cookie2), '->matchCookie() returns false if both cookies are not identical');
    }

    public function testHasAndGetMaxAge()
    {
        $cookie = new Cookie('MyCookie', 'MyValue');
        $this->assertTrue($cookie->hasMaxAge(), '->hasMaxAge() returns true if max age is not empty');

        $cookie = new Cookie('Cookie', 'Value', new DateTime(3600));
        $this->assertFalse($cookie->hasMaxAge(), '->hasMaxAge() returns false if max age is empty');
        $this->assertEquals(null, $cookie->getMaxAge(), '->getMaxAge() returns max age null if time is a DateTime object');

        $cookie = new Cookie('Cookie', 'Value', 3600);
        $this->assertEquals(3600, $cookie->getMaxAge(), '->getMaxAge() returns max age value if is set');
    }

    public function testWithMaxAge()
    {
        $cookie = new Cookie('Cookie', 'Value');
        $cookie = $cookie->withMaxAge(3600);
        $this->assertEquals(3600, $cookie->getMaxAge(), '->getMaxAge() returns max age value if is set');
    }

    public function testGetExpiresTime()
    {
        $time = new DateTime(3600);
        $cookie = new Cookie('foo', 'bar', $time);

        $this->assertInstanceOf('DateTime', $cookie->getExpiresTime(), '->getExpiresTime() returns \DateTime');
        $this->assertEquals($time->format('s'), $cookie->getExpiresTime()->format('s'), '->getExpiresTime() returns the expire date');
    }

    public function testWithExpiration()
    {
        $cookie = new Cookie('Cookie', 'Value');
        $cookie = $cookie->withExpiration(3600);

        $this->assertEquals(3600, $cookie->getMaxAge(), '->getMaxAge() returns max age value if is set');
        $this->assertInstanceOf('DateTime', $cookie->getExpiresTime(), '->getExpiresTime() returns \DateTime');
    }

    public function testWithExpires()
    {
        $expire = new DateTime('+1 day');
        $cookie = new Cookie('foo', 'bar', new DateTime());

        $cookie = $cookie->withExpires($expire);

        $this->assertInstanceOf('DateTime', $cookie->getExpiresTime(), '->getExpiresTime() returns \DateTime');
        $this->assertEquals($expire->format('U'), $cookie->getExpiresTime()->format('U'), '->getExpiresTime() returns the expire date');
    }

    public function testConstructorWithDateTime()
    {
        $expire = new DateTime();
        $cookie = new Cookie('foo', 'bar', $expire);

        $this->assertInstanceOf('DateTime', $cookie->getExpiresTime(), '->getExpiresTime() returns \DateTime');
        $this->assertEquals($expire->format('U'), $cookie->getExpiresTime()->format('U'), '->getExpiresTime() returns the expire date');
    }

    public function testGetExpiresTimeWithStringValue()
    {
        $value = '+1 day';
        $cookie = new Cookie('foo', 'bar', new DateTime($value));
        $expire = strtotime($value);

        $this->assertEquals($expire, $cookie->getExpiresTime()->format('U'), '->getExpiresTime() returns the expire date', 1);
    }

    public function testWithDomain()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.cOm');
        $cookie = $cookie->withDomain('google.com');

        $this->assertEquals(
            'google.com',
            $cookie->getDomain(),
            '->getDomain() returns the domain name on which the cookie is valid'
        );
    }

    public function testGetHasDomain()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.cOm');

        $this->assertEquals(
            'myfoodomain.com',
            $cookie->getDomain(),
            '->getDomain() returns the domain name on which the cookie is valid'
        );

        $this->assertTrue($cookie->hasDomain(), '->hasDomain() returns true if domain is set');

        $cookie = new Cookie('foo', 'bar', 0, '/');

        $this->assertFalse($cookie->hasDomain(), '->hasDomain() returns false if domain is not set');
    }

    public function testMatchDomain()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.com');

        $this->assertTrue($cookie->matchDomain('myfoodomain.com'), '->matchDomain() returns true if both cookies are identical');
        $this->assertTrue($cookie->matchDomain('www.myfoodomain.com'), '->matchDomain() returns true if both cookies are identical');
    }

    public function testMatchDomainToReturnFalseIfIp()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.myfoodomain.com');

        $this->assertFalse($cookie->matchDomain('127.0.0.1'), '->matchDomain() returns false if match is a IP');
    }

    public function testIsSecure()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.myfoodomain.com', true);

        $this->assertTrue($cookie->isSecure(), '->isSecure() returns whether the cookie is transmitted over HTTPS');

        $cookie = $cookie->withSecure(false);

        $this->assertFalse($cookie->isSecure(), '->isSecure() returns whether the cookie is transmitted over HTTPS');
    }

    public function testIsHttpOnly()
    {
        $cookie = new Cookie('foo', 'bar', 0, '/', '.myfoodomain.com', false, true);

        $this->assertTrue(
            $cookie->isHttpOnly(),
            '->isHttpOnly() returns whether the cookie is only transmitted over HTTP'
        );

        $cookie = $cookie->withHttpOnly(false);

        $this->assertFalse(
            $cookie->isHttpOnly(),
            '->isHttpOnly() returns whether the cookie is only transmitted over HTTP'
        );
    }

    public function testCookieIsNotExpired()
    {
        $cookie = new Cookie('foo', 'bar', new DateTime(time() + 3600 * 24));

        $this->assertFalse($cookie->isExpired(), '->isExpired() returns false if the cookie did not expire yet');
        $this->assertTrue($cookie->hasExpires(), '->hasExpires() returns true if the cookie has a expire time set');
    }

    public function testCookieIsExpired()
    {
        $cookie = new Cookie('foo', 'bar', -1);

        $this->assertTrue($cookie->isExpired(), '->isExpired() returns true if the cookie has expired');
    }

    public function testToString()
    {
        $time = new DateTime('Fri, 20-May-2011 15:25:52 GMT');
        $cookie = new Cookie('foo', 'bar', $time, '/', '.myfoodomain.com', true, true);
        $this->assertEquals(
            'foo=bar; Expires=Fri, 20-May-2011 15:25:52 GMT; Path=/; Domain=myfoodomain.com; Secure; HttpOnly',
            $cookie->__toString(),
            '->__toString() returns string representation of the cookie'
        );

        $cookie = new Cookie('foo', null, 1, '/admin/', '.myfoodomain.com', false, true);
        $this->assertEquals(
            'foo=deleted; Expires='.gmdate(
                'D, d-M-Y H:i:s T',
                time() - 31536001
            ).'; Path=/admin; Domain=myfoodomain.com; Max-Age=1; HttpOnly',
            $cookie->__toString(),
            '->__toString() returns string representation of a cleared cookie if value is NULL'
        );

        $cookie = new Cookie('foo', 'bar', 0, '/', '');
        $this->assertEquals(
            'foo=bar; Expires=Fri, 13-Dec-1901 20:45:53 UTC; Path=/',
            $cookie->__toString()
        );
    }
}
