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
            $this->assertEquals($expectedCookies[$name]->getName(), $cookie->getName());
            $this->assertEquals($expectedCookies[$name]->getValue(), $cookie->getValue());
            $this->assertEquals($expectedCookies[$name]->getDomain(), $cookie->getDomain());
            $this->assertEquals($expectedCookies[$name]->getMaxAge(), $cookie->getMaxAge());
            $this->assertEquals($expectedCookies[$name]->getPath(), $cookie->getPath());
            $this->assertEquals($expectedCookies[$name]->isSecure(), $cookie->isSecure());
            $this->assertEquals($expectedCookies[$name]->isHttpOnly(), $cookie->isHttpOnly());
            $this->assertEquals($expectedCookies[$name]->getSameSite(), $cookie->getSameSite());
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

        $this->assertEquals($expectedCookie->getName(), $cookies->get($cookieName)->getName());
        $this->assertEquals($expectedCookie->getValue(), $cookies->get($cookieName)->getValue());
        $this->assertEquals($expectedCookie->getDomain(), $cookies->get($cookieName)->getDomain());
        $this->assertEquals($expectedCookie->getMaxAge(), $cookies->get($cookieName)->getMaxAge());
        $this->assertEquals($expectedCookie->getPath(), $cookies->get($cookieName)->getPath());
        $this->assertEquals($expectedCookie->isSecure(), $cookies->get($cookieName)->isSecure());
        $this->assertEquals($expectedCookie->isHttpOnly(), $cookies->get($cookieName)->isHttpOnly());
        $this->assertEquals($expectedCookie->getSameSite(), $cookies->get($cookieName)->getSameSite());
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
            $this->assertTrue($setCookies->has($expectedSetCookie->getName()));
        }

        $this->assertFalse($setCookies->has('i know this cookie does not exist'));
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
