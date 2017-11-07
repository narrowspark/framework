<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class RequestCookiesTest extends MockeryTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The object [Viserio\Component\Cookie\SetCookie] must be an instance of [Viserio\Component\Cookie\Cookie].
     */
    public function testRequestCookiesToThrowException(): void
    {
        new RequestCookies([new SetCookie('test', 'test')]);
    }

    public function testAddCookieToHeaderAndBack(): void
    {
        $cookie  = new Cookie('encrypted', 'jiafs89320jadfa');
        $cookie2 = new Cookie('encrypted2', 'jiafs89320jadfa');

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);
        /** @var RequestCookies $cookies */
        $cookies = RequestCookies::fromRequest($request);
        $cookies = $cookies->add($cookie);
        $cookies = $cookies->add($cookie2);
        $request = $cookies->renderIntoCookieHeader($request);
        $cookies = RequestCookies::fromRequest($request);

        self::assertSame($cookie->getName(), $cookies->get('encrypted')->getName());
        self::assertSame($cookie->getValue(), $cookies->get('encrypted')->getValue());
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     *
     * Cant test with automatic expires, test are one sec to slow.
     *
     * @param mixed $cookieString
     * @param array $expectedCookies
     */
    public function testFromCookieHeaderWithoutExpire($cookieString, array $expectedCookies): void
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')
            ->with('cookie')
            ->andReturn($cookieString);

        $cookies = RequestCookies::fromRequest($request);

        /** @var Cookie $cookie */
        foreach ($cookies->getAll() as $name => $cookie) {
            self::assertEquals($expectedCookies[$name]->getName(), $cookie->getName());
            self::assertEquals($expectedCookies[$name]->getValue(), $cookie->getValue());
        }
    }

    /**
     * @dataProvider provideGetsCookieByNameData
     *
     * @param string $cookieString
     * @param string $cookieName
     * @param Cookie $expectedCookie
     */
    public function testItGetsCookieByName(string $cookieString, string $cookieName, Cookie $expectedCookie): void
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')
            ->with('cookie')
            ->andReturn($cookieString);

        $cookies = RequestCookies::fromRequest($request);

        self::assertEquals($expectedCookie->getName(), $cookies->get($cookieName)->getName());
        self::assertEquals($expectedCookie->getValue(), $cookies->get($cookieName)->getValue());
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     *
     * @param string $setCookieStrings
     * @param array  $expectedSetCookies
     */
    public function testItKnowsWhichCookiesAreAvailable(string $setCookieStrings, array $expectedSetCookies): void
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('getHeaderLine')
            ->with('cookie')
            ->andReturn($setCookieStrings);

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
