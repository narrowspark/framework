<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests;

use Cake\Chronos\Chronos;
use DateTime;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Cookie\Cookie;
use Viserio\Cookie\RequestCookies;

class RequestCookiesTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     *
     * Cant test with automatic expires, test are one sec to slow.
     */
    public function testFromCookieHeaderWithoutExpire($cookieString, array $expectedCookies)
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')->with('Cookie')->andReturn($cookieString);

        $cookies = RequestCookies::fromRequest($request);

        foreach ($cookies->getAll() as $name => $cookie) {
            self::assertEquals($expectedCookies[$name]->getName(), $cookie->getName());
            self::assertEquals($expectedCookies[$name]->getValue(), $cookie->getValue());
            self::assertEquals($expectedCookies[$name]->getDomain(), $cookie->getDomain());
            self::assertEquals($expectedCookies[$name]->getMaxAge(), $cookie->getMaxAge());
            self::assertEquals($expectedCookies[$name]->getPath(), $cookie->getPath());
            self::assertEquals($expectedCookies[$name]->isSecure(), $cookie->isSecure());
            self::assertEquals($expectedCookies[$name]->isHttpOnly(), $cookie->isHttpOnly());
            self::assertEquals($expectedCookies[$name]->getSameSite(), $cookie->getSameSite());
        }
    }

    /**
     * @dataProvider provideGetsCookieByNameData
     */
    public function testItGetsCookieByName(string $cookieString, string $cookieName, Cookie $expectedCookie)
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')->with('Cookie')->andReturn($cookieString);

        $cookies = RequestCookies::fromRequest($request);

        self::assertEquals($expectedCookie->getName(), $cookies->get($cookieName)->getName());
        self::assertEquals($expectedCookie->getValue(), $cookies->get($cookieName)->getValue());
        self::assertEquals($expectedCookie->getDomain(), $cookies->get($cookieName)->getDomain());
        self::assertEquals($expectedCookie->getMaxAge(), $cookies->get($cookieName)->getMaxAge());
        self::assertEquals($expectedCookie->getPath(), $cookies->get($cookieName)->getPath());
        self::assertEquals($expectedCookie->isSecure(), $cookies->get($cookieName)->isSecure());
        self::assertEquals($expectedCookie->isHttpOnly(), $cookies->get($cookieName)->isHttpOnly());
        self::assertEquals($expectedCookie->getSameSite(), $cookies->get($cookieName)->getSameSite());
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     */
    public function testItKnowsWhichCookiesAreAvailable(string $setCookieStrings, array $expectedSetCookies)
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')->with('Cookie')->andReturn($setCookieStrings);

        $setCookies = RequestCookies::fromRequest($request);

        foreach ($expectedSetCookies as $expectedSetCookie) {
            self::assertTrue($setCookies->has($expectedSetCookie->getName()));
        }

        self::assertFalse($setCookies->has('i know this cookie does not exist'));
    }

    public function provideParsesFromCookieStringWithoutExpireData()
    {
        return [
            [
                'some;',
                [new Cookie('some')],
            ],
            [
                'someCookie=',
                [new Cookie('someCookie')],
            ],
            [
                'someCookie=someValue',
                [new Cookie('someCookie', 'someValue')],
            ],
            [
                'someCookie=someValue; someCookie3=someValue3',
                [
                    new Cookie('someCookie', 'someValue'),
                    new Cookie('someCookie3', 'someValue3'),
                ],
            ],
        ];
    }

    public function provideGetsCookieByNameData()
    {
        return [
            ['someCookie=someValue', 'someCookie', new Cookie('someCookie', 'someValue')],
            ['someCookie=', 'someCookie', new Cookie('someCookie')],
            ['hello=world; someCookie=someValue; token=abc123', 'someCookie', new Cookie('someCookie', 'someValue')],
            ['hello=world; someCookie=; token=abc123', 'someCookie', new Cookie('someCookie')],
        ];
    }
}
