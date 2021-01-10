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

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\Http\ServerRequest;
use Viserio\Contract\Cookie\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Cookie\AbstractCookieCollector
 * @covers \Viserio\Component\Cookie\RequestCookies
 */
final class RequestCookiesTest extends MockeryTestCase
{
    public function testRequestCookiesToThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The object [Viserio\\Component\\Cookie\\SetCookie] must be an instance of [Viserio\\Component\\Cookie\\Cookie].');

        new RequestCookies([new SetCookie('test', 'test')]);
    }

    public function testAddCookieToHeaderAndBack(): void
    {
        $cookie = new Cookie('encrypted', 'jiafs89320jadfa');
        $cookie2 = new Cookie('encrypted2', 'jiafs89320jadfa');

        $request = new ServerRequest('/');

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
     */
    public function testFromCookieHeaderWithoutExpire($cookieString, array $expectedCookies): void
    {
        $request = Mockery::mock(Request::class);
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
     * @dataProvider provideItGetsCookieByNameCases
     */
    public function testItGetsCookieByName(string $cookieString, string $cookieName, Cookie $expectedCookie): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getHeaderLine')
            ->with('cookie')
            ->andReturn($cookieString);

        $cookies = RequestCookies::fromRequest($request);

        self::assertEquals($expectedCookie->getName(), $cookies->get($cookieName)->getName());
        self::assertEquals($expectedCookie->getValue(), $cookies->get($cookieName)->getValue());
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     */
    public function testItKnowsWhichCookiesAreAvailable(string $setCookieStrings, array $expectedSetCookies): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getHeaderLine')
            ->with('cookie')
            ->andReturn($setCookieStrings);

        $setCookies = RequestCookies::fromRequest($request);

        foreach ($expectedSetCookies as $expectedSetCookie) {
            self::assertTrue($setCookies->has($expectedSetCookie->getName()));
        }

        self::assertFalse($setCookies->has('i know this cookie does not exist'));
    }

    public static function provideParsesFromCookieStringWithoutExpireData(): iterable
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

    public static function provideItGetsCookieByNameCases(): iterable
    {
        return [
            ['someCookie=someValue', 'someCookie', new Cookie('someCookie', 'someValue')],
            ['someCookie=', 'someCookie', new Cookie('someCookie')],
            ['hello=world; someCookie=someValue; token=abc123', 'someCookie', new Cookie('someCookie', 'someValue')],
            ['hello=world; someCookie=; token=abc123', 'someCookie', new Cookie('someCookie')],
        ];
    }

    public function testAddCookieToThrowExceptionOnInvalidObject(): void
    {
        $class = new class() {
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The object [%s] must be an instance of [%s] or [%s].', \get_class($class), Cookie::class, SetCookie::class));

        $request = new ServerRequest('/');

        $setCookies = RequestCookies::fromRequest($request);
        $setCookies->add($class);
    }
}
