<?php
namespace Viserio\Cookie\Test;

use DateTime;
use Mockery as Mock;
use Psr\Http\Message\ServerRequestInterface as Request;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\Cookie;

class CookieJarTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testCookiesAreCreatedWithProperOptions()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('foo', 'bar');

        $c = $cookie->create('color', 'blue', 10, '/path', '/domain', true, false);
        $this->assertEquals('blue', $c->getValue());
        $this->assertFalse($c->isHttpOnly());
        $this->assertTrue($c->isSecure());
        $this->assertEquals('/domain', $c->getDomain());
        $this->assertEquals('/path', $c->getPath());

        $c2 = $cookie->forever('color', 'blue', '/path', '/domain', true, false);
        $this->assertEquals('blue', $c2->getValue());
        $this->assertFalse($c2->isHttpOnly());
        $this->assertTrue($c2->isSecure());
        $this->assertEquals('/domain', $c2->getDomain());
        $this->assertEquals('/path', $c2->getPath());

        $c3 = $cookie->forget('color');
        $this->assertNull($c3->getValue());
        $this->assertTrue($c3->getExpiresTime()->getTimestamp() < time());
    }

    public function testCookiesAreCreatedWithProperOptionsUsingDefaultPathAndDomain()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('/path', '/domain');

        $c = $cookie->create('color', 'blue', 10, null, null, true, false);
        $this->assertEquals('blue', $c->getValue());
        $this->assertFalse($c->isHttpOnly());
        $this->assertTrue($c->isSecure());
        $this->assertEquals('/domain', $c->getDomain());
        $this->assertEquals('/path', $c->getPath());
    }

    public function testQueuedCookies()
    {
        $cookie = $this->getCreator();
        $this->assertEmpty($cookie->getQueuedCookies());
        $this->assertFalse($cookie->hasQueued('foo'));

        $cookie->queue($cookie->create('foo', 'bar'));
        $this->assertArrayHasKey('foo', $cookie->getQueuedCookies());
        $this->assertTrue($cookie->hasQueued('foo'));
        $this->assertInstanceOf('Viserio\Cookie\Cookie', $cookie->queued('foo'));

        $cookie->queue('qu', 'ux');
        $this->assertArrayHasKey('qu', $cookie->getQueuedCookies());
        $this->assertTrue($cookie->hasQueued('qu'));
        $this->assertInstanceOf('Viserio\Cookie\Cookie', $cookie->queued('qu'));
    }

    public function testUnqueue()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        $this->assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        $this->assertEmpty($cookie->getQueuedCookies());
    }

    /**
     * @dataProvider provideParsesFromCookieStringData
     */
    public function testFromServerRequest($cookieString, Cookie $expectedCookie)
    {
        $request = Mock::mock(Request::class);
        $request->shouldReceive('getHeader')->with('Set-Cookie')->andReturn($cookieString);

        $cookie = $this->getCreator();
        $setCookie = $cookie->fromServerRequest($request);

        $this->assertEquals($expectedCookie, $setCookie);
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }

    public function provideParsesFromCookieStringData()
    {
        return [
            [
                'someCookie=',
                new Cookie('someCookie')
            ],
            [
                'someCookie=someValue',
                (new Cookie('someCookie'))
                    ->withValue('someValue')
            ],
            [
                'LSID=DQAAAK%2FEaem_vYg; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/accounts; Secure; HttpOnly',
                (new Cookie('LSID'))
                    ->withValue('DQAAAK/Eaem_vYg')
                    ->withPath('/accounts')
                    ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                    ->withSecure(true)
                    ->withHttpOnly(true)
            ],
            [
                'HSID=AYQEVn%2F.DKrdst; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/; Domain=foo.com; HttpOnly',
                (new Cookie('HSID'))
                    ->withValue('AYQEVn/.DKrdst')
                    ->withDomain('.foo.com')
                    ->withPath('/')
                    ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                    ->withHttpOnly(true)
            ],
            [
                'SSID=Ap4P%2F.GTEq; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Path=/; Domain=foo.com; Secure; HttpOnly',
                (new Cookie('SSID'))
                    ->withValue('Ap4P/.GTEq')
                    ->withDomain('foo.com')
                    ->withPath('/')
                    ->withExpires(new DateTime('Wed, 13 Jan 2021 22:23:01 GMT'))
                    ->withSecure(true)
                    ->withHttpOnly(true)
            ],
            [
                'lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; HttpOnly',
                (new Cookie('lu'))
                    ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
                    ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
                    ->withPath('/')
                    ->withDomain('.example.com')
                    ->withHttpOnly(true)
            ],
            // TODO test MaxAge
            // [
            //     'lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Max-Age=500; Secure; HttpOnly',
            //     (new Cookie('lu'))
            //         ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
            //         ->withMaxAge(500)
            //         ->withPath('/')
            //         ->withDomain('.example.com')
            //         ->withSecure(true)
            //         ->withHttpOnly(true)
            // ],
            // [
            //     'lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly',
            //     (new Cookie('lu'))
            //         ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
            //         ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
            //         ->withMaxAge(500)
            //         ->withPath('/')
            //         ->withDomain('.example.com')
            //         ->withSecure(true)
            //         ->withHttpOnly(true)
            // ],
            // [
            //     'lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly',
            //     (new Cookie('lu'))
            //         ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
            //         ->withExpires(new DateTime(1358286458))
            //         ->withMaxAge(500)
            //         ->withPath('/')
            //         ->withDomain('.example.com')
            //         ->withSecure(true)
            //         ->withHttpOnly(true)
            // ],
            // [
            //     'lu=Rg3vHJZnehYLjVg7qi3bZjzg; Domain=.example.com; Path=/; Expires=Tue, 15 Jan 2013 21:47:38 GMT; Max-Age=500; Secure; HttpOnly',
            //     (new Cookie('lu'))
            //         ->withValue('Rg3vHJZnehYLjVg7qi3bZjzg')
            //         ->withExpires(new DateTime('Tue, 15-Jan-2013 21:47:38 GMT'))
            //         ->withMaxAge(500)
            //         ->withPath('/')
            //         ->withDomain('.example.com')
            //         ->withSecure(true)
            //         ->withHttpOnly(true)
            // ],
        ];
    }
}
