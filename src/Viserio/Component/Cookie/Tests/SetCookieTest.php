<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use Cake\Chronos\Chronos;
use DateTime;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\SetCookie;

/**
 * @internal
 */
final class SetCookieTest extends TestCase
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
     *
     * @param mixed $name
     */
    public function testInstantiationThrowsExceptionIfCookieNameContainsInvalidCharacters($name): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new SetCookie($name);
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

        new SetCookie('MyCookie', $value);
    }

    public function testGetValue(): void
    {
        $value  = 'MyValue';
        $cookie = new SetCookie('MyCookie', $value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
        $this->assertTrue($cookie->hasValue(), '->hasValue() returns true if the value exist');
    }

    public function testWithValue(): void
    {
        $value  = 'MyValue';
        $cookie = new SetCookie('MyCookie');
        $cookie = $cookie->withValue($value);

        $this->assertSame($value, $cookie->getValue(), '->getValue() returns the proper value');
    }

    public function testGetPatchAndWithPath(): void
    {
        $cookie = new SetCookie('foo', 'bar');

        $this->assertSame('/', $cookie->getPath(), '->getPath() returns / as the default path');

        $cookie = $cookie->withPath('/tests/');

        $this->assertSame('/tests', $cookie->getPath(), '->getPath() returns / as the default path');
    }

    public function testMatchPath(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/');

        $this->assertTrue($cookie->matchPath('/'), '->matchPath() returns true if the paths match');
        $this->assertFalse(
            $cookie->matchPath('/path/to/somewhere'),
            '->matchPath() returns false if the paths not match'
        );
    }

    public function testMatchCookie(): void
    {
        $cookie  = new SetCookie('foo', 'bar', 0, '/');
        $cookie2 = new SetCookie('bar', 'foo', 0, '/');

        $this->assertTrue($cookie->matchCookie($cookie), '->matchCookie() returns true if both cookies are identical');
        $this->assertFalse(
            $cookie->matchCookie($cookie2),
            '->matchCookie() returns false if both cookies are not identical'
        );
    }

    public function testHasAndGetMaxAge(): void
    {
        $cookie = new SetCookie('MyCookie', 'MyValue');
        $this->assertTrue($cookie->hasMaxAge(), '->hasMaxAge() returns true if max age is not empty');

        $cookie = new SetCookie('Cookie', 'Value', new DateTime('3600'));
        $this->assertFalse($cookie->hasMaxAge(), '->hasMaxAge() returns false if max age is empty');
        $this->assertNull(
            $cookie->getMaxAge(),
            '->getMaxAge() returns max age null if time is a DateTime object'
        );

        $cookie = new SetCookie('Cookie', 'Value', 3600);
        $this->assertEquals(3600, $cookie->getMaxAge(), '->getMaxAge() returns max age value if is set');
    }

    public function testWithMaxAge(): void
    {
        $cookie = new SetCookie('Cookie', 'Value');
        $cookie = $cookie->withMaxAge(3600);
        $this->assertEquals(3600, $cookie->getMaxAge(), '->getMaxAge() returns max age value if is set');
    }

    public function testWithExpires(): void
    {
        $expire = Chronos::now();
        $expire = $expire->addDay(1);
        $cookie = new SetCookie('foo', 'bar', Chronos::now());
        $cookie = $cookie->withExpires($expire);

        $this->assertEquals(
            \strtotime($expire->toCookieString()),
            $cookie->getExpiresTime(),
            '->getExpiresTime() returns the expire date'
        );
    }

    public function testConstructorWithDateTime(): void
    {
        $expire = Chronos::now();
        $cookie = new SetCookie('foo', 'bar', $expire);

        $this->assertEquals(
            \strtotime($expire->toCookieString()),
            $cookie->getExpiresTime(),
            '->getExpiresTime() returns the expire date'
        );
    }

    public function testGetExpiresWithChronosTimestamp(): void
    {
        $expire = Chronos::now()->addSeconds(7200);
        $cookie = new SetCookie('foo', 'bar', $expire->getTimestamp());

        $this->assertEquals(
            \strtotime($expire->toCookieString()),
            $cookie->getExpiresTime(),
            '->getExpiresTime() returns the expire date'
        );
    }

    public function testGetExpiresWithSeconds(): void
    {
        $expire = 7200;
        $cookie = new SetCookie('foo', 'bar', $expire);

        $this->assertEquals(
            Chronos::now()->getTimestamp() + $expire,
            $cookie->getExpiresTime(),
            '->getExpiresTime() returns the expire date'
        );
    }

    public function testGetExpiresTimeWithStringValue(): void
    {
        $expire = new Chronos('+1 day');
        $cookie = new SetCookie('foo', 'bar', $expire);

        $this->assertEquals(
            \strtotime($expire->toCookieString()),
            $cookie->getExpiresTime(),
            '->getExpiresTime() returns the expire date',
            1
        );
    }

    public function testWithDomain(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.cOm');
        $cookie = $cookie->withDomain('google.com');

        $this->assertEquals(
            'google.com',
            $cookie->getDomain(),
            '->getDomain() returns the domain name on which the cookie is valid'
        );
    }

    public function testGetHasDomain(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.cOm');

        $this->assertEquals(
            'myfoodomain.com',
            $cookie->getDomain(),
            '->getDomain() returns the domain name on which the cookie is valid'
        );

        $this->assertTrue($cookie->hasDomain(), '->hasDomain() returns true if domain is set');

        $cookie = new SetCookie('foo', 'bar', 0, '/');

        $this->assertFalse($cookie->hasDomain(), '->hasDomain() returns false if domain is not set');
    }

    public function testMatchDomain(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.MyFooDoMaiN.com');

        $this->assertTrue(
            $cookie->matchDomain('myfoodomain.com'),
            '->matchDomain() returns true if both cookies are identical'
        );
        $this->assertTrue(
            $cookie->matchDomain('www.myfoodomain.com'),
            '->matchDomain() returns true if both cookies are identical'
        );
    }

    public function testMatchDomainToReturnFalseIfIp(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.myfoodomain.com');

        $this->assertFalse($cookie->matchDomain('127.0.0.1'), '->matchDomain() returns false if match is a IP');
    }

    public function testIsSecure(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.myfoodomain.com', true);

        $this->assertTrue($cookie->isSecure(), '->isSecure() returns whether the cookie is transmitted over HTTPS');

        $cookie = $cookie->withSecure(false);

        $this->assertFalse($cookie->isSecure(), '->isSecure() returns whether the cookie is transmitted over HTTPS');
    }

    public function testIsHttpOnly(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.myfoodomain.com', false, true);

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

    public function testIsSameSite(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.myfoodomain.com', false, true, SetCookie::SAMESITE_STRICT);

        $this->assertTrue(
            $cookie->isSameSite(),
            '->isSameSite() returns whether the cookie is set with samesite value'
        );

        $cookie = $cookie->withSameSite(false);

        $this->assertFalse(
            $cookie->isSameSite(),
            '->isHttpOnly() returns whether the cookie is send normal without samesite'
        );
    }

    public function testGetSameSite(): void
    {
        $cookie = new SetCookie('foo', 'bar', 0, '/', '.myfoodomain.com', false, true, SetCookie::SAMESITE_STRICT);

        $this->assertSame(
            SetCookie::SAMESITE_STRICT,
            $cookie->getSameSite(),
            '->getSameSite() returns cookies samesite which is set to strict'
        );

        $cookie = $cookie->withSameSite(SetCookie::SAMESITE_LAX);

        $this->assertSame(
            SetCookie::SAMESITE_LAX,
            $cookie->getSameSite(),
            '->getSameSite() returns cookies samesite which is set to lax'
        );
    }

    public function testCookieIsNotExpired(): void
    {
        $cookie = new SetCookie('foo', 'bar', new DateTime('+360 day'));

        $this->assertFalse($cookie->isExpired(), '->isExpired() returns false if the cookie did not expire yet');
        $this->assertTrue($cookie->hasExpires(), '->hasExpires() returns true if the cookie has a expire time set');
    }

    public function testCookieIsExpired(): void
    {
        $cookie = new SetCookie('foo', 'bar', -100);

        $this->assertTrue($cookie->isExpired(), '->isExpired() returns true if the cookie has expired');
    }

    public function testToString(): void
    {
        $time   = new DateTime('Fri, 20-May-2011 15:25:52 GMT');
        $cookie = new SetCookie('foo', 'bar', $time, '/', '.myfoodomain.com', true, true, SetCookie::SAMESITE_STRICT);
        $this->assertEquals(
            'foo=bar; Expires=' . (new Chronos(\gmdate('D, d-M-Y H:i:s', $time->getTimestamp())))->toCookieString() . '; Path=/; Domain=myfoodomain.com; Secure; HttpOnly; SameSite=strict',
            $cookie->__toString(),
            '->__toString() returns string representation of the cookie'
        );

        $cookie = new SetCookie('foo', null, 1, '/admin/', '.myfoodomain.com', false, true);
        $this->assertEquals(
            'foo=deleted; Expires=' . (new Chronos(\gmdate(
                'D, d-M-Y H:i:s T',
                Chronos::now()->getTimestamp() - 31536001
            )))->toCookieString() . '; Path=/admin; Domain=myfoodomain.com; Max-Age=1; HttpOnly',
            $cookie->__toString(),
            '->__toString() returns string representation of a cleared cookie if value is NULL'
        );

        $cookie = new SetCookie('foo');
        $this->assertEquals(
            'foo=deleted; Expires=' . (new Chronos(\gmdate(
                'D, d-M-Y H:i:s T',
                Chronos::now()->getTimestamp() - 31536001
            )))->toCookieString() . '; Path=/',
            $cookie->__toString()
        );
    }
}
