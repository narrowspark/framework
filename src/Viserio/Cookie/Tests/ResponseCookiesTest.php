<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests;

use Cake\Chronos\Chronos;
use DateTime;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Viserio\Cookie\Cookie;
use Viserio\Cookie\ResponseCookies;
use Viserio\HttpFactory\ResponseFactory;

class ResponseCookiesTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @dataProvider provideParsesFromCookieStringData
     */
    public function testFromSetCookieHeader(array $cookieStrings, array $expectedCookies)
    {
        $response = $this->mock(Response::class);
        $response->shouldReceive('getHeader')->with('Set-Cookie')->andReturn($cookieStrings);

        $setCookies = ResponseCookies::fromResponse($response);

        foreach ($setCookies->getAll() as $name => $cookie) {
            self::assertEquals($expectedCookies[$name], $cookie);
        }
    }

    /**
     * @dataProvider provideParsesFromCookieStringWithoutExpireData
     *
     * Cant test with automatic expires, test are one sec to slow.
     */
    public function testFromSetCookieHeaderWithoutExpire(array $cookieStrings, array $expectedCookies)
    {
        $response = $this->mock(Response::class);
        $response->shouldReceive('getHeader')->with('Set-Cookie')->andReturn($cookieStrings);

        $setCookies = ResponseCookies::fromResponse($response);

        foreach ($setCookies->getAll() as $name => $cookie) {
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

    public function provideParsesFromCookieStringData()
    {
        return [
            [
                ['LSID=DQAAAK%2FEaem_vYg; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/accounts; Secure; HttpOnly'],
                [
                    (new Cookie('LSID'))
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
                   (new Cookie('HSID'))
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
                    (new Cookie('SSID'))
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
                    (new Cookie('lu'))
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
                    (new Cookie('lu'))
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
                    (new Cookie('lu'))
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
                    new Cookie('a', 'AAA'),
                    new Cookie('b', 'BBB'),
                    new Cookie('c', 'CCC'),
                ],
            ],
            [
                ['some;'],
                [new Cookie('some')],
            ],
            [
                ['someCookie='],
                [new Cookie('someCookie')],
            ],
            [
                ['someCookie=someValue'],
                [
                    (new Cookie('someCookie'))->withValue('someValue'),
                ],
            ],
            [
                ['lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Max-Age=500; Secure; HttpOnly'],
                [
                    (new Cookie('lu'))
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
                    (new Cookie('lu'))
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
                [(new Cookie('lu'))
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
                    new Cookie('someCookie', 'someValue'),
                    (new Cookie('HSID'))
                        ->withValue('AYQEVn/.DKrdst')
                        ->withDomain('.foo.com')
                        ->withPath('/accounts')
                        ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                        ->withHttpOnly(true),
                ],
            ],
        ];
    }

    public function testGetsAndUpdatesSetCookieValueOnResponse()
    {
        $response = (new ResponseFactory())->createResponse();
        $response = $response->withAddedHeader('Set-Cookie', 'theme=light');
        $response = $response->withAddedHeader('Set-Cookie', 'sessionToken=ENCRYPTED');
        $response = $response->withAddedHeader('Set-Cookie', 'hello=world');

        $setCookies = ResponseCookies::fromResponse($response);

        $decryptedSessionToken = $setCookies->get('sessionToken');
        $decryptedValue = $decryptedSessionToken->getValue();
        $encryptedValue = str_rot13($decryptedValue);
        $encryptedSessionToken = $decryptedSessionToken->withValue($encryptedValue);
        $setCookies = $setCookies->add($encryptedSessionToken);
        $setCookies = $setCookies->forget('hello');

        self::assertFalse($setCookies->has('hello'));
        self::assertTrue(is_null($setCookies->get('hello')));

        $response = $setCookies->renderIntoSetCookieHeader($response);

        self::assertSame('theme=light', $this->splitOnAttributeDelimiter($response->getHeader('Set-Cookie')[0])[0]);
        self::assertSame('sessionToken=RAPELCGRQ', $this->splitOnAttributeDelimiter($response->getHeader('Set-Cookie')[1])[0]);
    }

    protected function splitOnAttributeDelimiter(string $string): array
    {
        return array_filter(preg_split('@\s*[;]\s*@', $string));
    }
}
