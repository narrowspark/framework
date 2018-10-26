<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use DateTime;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface as Response;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class ResponseCookiesTest extends MockeryTestCase
{
    public function testRequestCookiesToThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The object [Viserio\\Component\\Cookie\\Cookie] must implement [Viserio\\Component\\Contract\\Cookie\\Cookie].');

        new ResponseCookies([new Cookie('test', 'test')]);
    }

    /**
     * @dataProvider provideParsesFromCookieStringData
     *
     * @param array $cookieStrings
     * @param array $expectedCookies
     */
    public function testFromSetCookieHeader(array $cookieStrings, array $expectedCookies): void
    {
        $response = $this->mock(Response::class);
        $response->shouldReceive('getHeader')->with('set-cookie')->andReturn($cookieStrings);

        $setCookies = ResponseCookies::fromResponse($response);

        foreach ($setCookies->getAll() as $name => $cookie) {
            $this->assertEquals($expectedCookies[$name], $cookie);
        }
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     *
     * Cant test with automatic expires, test are one sec to slow.
     *
     * @param array $cookieStrings
     * @param array $expectedCookies
     */
    public function testFromSetCookieHeaderWithoutExpire(array $cookieStrings, array $expectedCookies): void
    {
        $response = $this->mock(Response::class);
        $response->shouldReceive('getHeader')->with('set-cookie')->andReturn($cookieStrings);

        $setCookies = ResponseCookies::fromResponse($response);

        /** @var SetCookie $cookie */
        foreach ($setCookies->getAll() as $name => $cookie) {
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

    public function provideParsesFromCookieStringData()
    {
        return [
            [
                ['LSID=DQAAAK%2FEaem_vYg; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/accounts; Secure; HttpOnly'],
                [
                    (new SetCookie('LSID'))
                        ->withValue('DQAAAK/Eaem_vYg')
                        ->withPath('/accounts')
                        ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['HSID=AYQEVn%2F.DKrdst; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/; Domain=foo.com; HttpOnly'],
                [
                    (new SetCookie('HSID'))
                        ->withValue('AYQEVn/.DKrdst')
                        ->withDomain('.foo.com')
                        ->withPath('/')
                        ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['SSID=Ap4P%2F.GTEq; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/; Domain=foo.com; Secure; HttpOnly'],
                [
                    (new SetCookie('SSID'))
                        ->withValue('Ap4P/.GTEq')
                        ->withDomain('foo.com')
                        ->withPath('/')
                        ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly'],
                [
                    (new SetCookie('lu'))
                        ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                        ->withExpires(new DateTime('Tue, 15 Jan 2013 21:47:38 GMT'))
                        ->withMaxAge(500)
                        ->withPath('/')
                        ->withDomain('.example.com')
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly'],
                [
                    (new SetCookie('lu'))
                        ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                        ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
                        ->withMaxAge(500)
                        ->withPath('/')
                        ->withDomain('.example.com')
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly; SameSite=strict'],
                [
                    (new SetCookie('lu'))
                        ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                        ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
                        ->withMaxAge(500)
                        ->withPath('/')
                        ->withDomain('.example.com')
                        ->withSecure(true)
                        ->withHttpOnly(true)
                        ->withSameSite('strict'),
                ],
            ],
        ];
    }

    public function provideParsesFromCookieStringWithoutExpireData()
    {
        return [
            [
                [],
                [],
            ],
            [
                [
                    'a=AAA',
                    'b=BBB',
                    'c=CCC',
                ],
                [
                    new SetCookie('a', 'AAA'),
                    new SetCookie('b', 'BBB'),
                    new SetCookie('c', 'CCC'),
                ],
            ],
            [
                ['some;'],
                [new SetCookie('some')],
            ],
            [
                ['someCookie='],
                [new SetCookie('someCookie')],
            ],
            [
                ['someCookie=someValue'],
                [
                    (new SetCookie('someCookie'))->withValue('someValue'),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Max-Age=500; Secure; HttpOnly'],
                [
                    (new SetCookie('lu'))
                        ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                        ->withMaxAge(500)
                        ->withPath('/')
                        ->withDomain('.example.com')
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Max-Age=500; Secure; HttpOnly'],
                [
                    (new SetCookie('lu'))
                        ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                        ->withMaxAge(500)
                        ->withPath('/')
                        ->withDomain('.example.com')
                        ->withSecure(true)
                        ->withHttpOnly(true),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly'],
                [(new SetCookie('lu'))
                    ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                    ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
                    ->withMaxAge(500)
                    ->withPath('/')
                    ->withDomain('.example.com')
                    ->withSecure(true)
                    ->withHttpOnly(true),
                ],
            ],
            [
                [
                    'someCookie=someValue',
                    'HSID=AYQEVn%2F.DKrdst; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/accounts; Domain=foo.com; HttpOnly',
                ],
                [
                    new SetCookie('someCookie', 'someValue'),
                    (new SetCookie('HSID'))
                        ->withValue('AYQEVn/.DKrdst')
                        ->withDomain('.foo.com')
                        ->withPath('/accounts')
                        ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                        ->withHttpOnly(true),
                ],
            ],
        ];
    }

    public function testGetsAndUpdatesSetCookieValueOnResponse(): void
    {
        $response = (new ResponseFactory())->createResponse();
        $response = $response->withAddedHeader('Set-Cookie', 'theme=light');
        $response = $response->withAddedHeader('Set-Cookie', 'sessionToken=ENCRYPTED');
        $response = $response->withAddedHeader('Set-Cookie', 'hello=world');

        $setCookies = ResponseCookies::fromResponse($response);

        $decryptedSessionToken = $setCookies->get('sessionToken');
        $decryptedValue        = $decryptedSessionToken->getValue();
        $encryptedValue        = \str_rot13($decryptedValue);
        $encryptedSessionToken = $decryptedSessionToken->withValue($encryptedValue);

        $setCookies = $setCookies->add($encryptedSessionToken);
        $setCookies = $setCookies->forget('hello');

        $this->assertFalse($setCookies->has('hello'));
        $this->assertNull($setCookies->get('hello'));

        $response = $setCookies->renderIntoSetCookieHeader($response);

        $this->assertSame('theme=light', $this->splitOnAttributeDelimiter($response->getHeader('Set-Cookie')[0])[0]);
        $this->assertSame('sessionToken=RAPELCGRQ', $this->splitOnAttributeDelimiter($response->getHeader('Set-Cookie')[1])[0]);
    }

    protected function splitOnAttributeDelimiter(string $string): array
    {
        return \array_filter(\preg_split('@\s*[;]\s*@', $string));
    }
}
