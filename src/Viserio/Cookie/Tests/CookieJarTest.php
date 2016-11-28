<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Cookie\CookieJar;

class CookieJarTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testCookiesAreCreatedWithProperOptions()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('foo', 'bar');

        $c = $cookie->create('color', 'blue', 10, '/path', '/domain', true, false);
        self::assertEquals('blue', $c->getValue());
        self::assertFalse($c->isHttpOnly());
        self::assertTrue($c->isSecure());
        self::assertEquals('/domain', $c->getDomain());
        self::assertEquals('/path', $c->getPath());

        $c2 = $cookie->forever('color', 'blue', '/path', '/domain', true, false);
        self::assertEquals('blue', $c2->getValue());
        self::assertFalse($c2->isHttpOnly());
        self::assertTrue($c2->isSecure());
        self::assertEquals('/domain', $c2->getDomain());
        self::assertEquals('/path', $c2->getPath());

        $c3 = $cookie->delete('color');
        self::assertNull($c3->getValue());
        self::assertNotEquals($c3->getExpiresTime(), Chronos::now()->getTimestamp());
    }

    public function testCookiesAreCreatedWithProperOptionsUsingDefaultPathAndDomain()
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('/path', '/domain');

        $c = $cookie->create('color', 'blue', 10, null, null, true, false);
        self::assertEquals('blue', $c->getValue());
        self::assertFalse($c->isHttpOnly());
        self::assertTrue($c->isSecure());
        self::assertEquals('/domain', $c->getDomain());
        self::assertEquals('/path', $c->getPath());
    }

    public function testQueuedCookies()
    {
        $cookie = $this->getCreator();
        self::assertEmpty($cookie->getQueuedCookies());
        self::assertFalse($cookie->hasQueued('foo'));

        $cookie->queue($cookie->create('foo', 'bar'));
        self::assertArrayHasKey('foo', $cookie->getQueuedCookies());
        self::assertTrue($cookie->hasQueued('foo'));
        self::assertInstanceOf('Viserio\Cookie\Cookie', $cookie->queued('foo'));

        $cookie->queue('qu', 'ux');
        self::assertArrayHasKey('qu', $cookie->getQueuedCookies());
        self::assertTrue($cookie->hasQueued('qu'));
        self::assertInstanceOf('Viserio\Contracts\Cookie\Cookie', $cookie->queued('qu'));
    }

    public function testUnqueue()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        self::assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        self::assertEmpty($cookie->getQueuedCookies());
    }

    public function testRenderIntoCookieHeader()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        $cookie->queue('qu', 'ux');

        $request = $this->mock(RequestInterface::class);
        $request->shouldReceive('getHeaders')
            ->andReturn(['Cookie' => implode('; ', $cookie->getQueuedCookies())]);
        $request->shouldReceive('withHeader')
            ->with('Cookie', implode('; ', $cookie->getQueuedCookies()))
            ->andReturn(clone $request);

        $requestWithCookie = $cookie->renderIntoCookieHeader($request);

        self::assertSame(['Cookie' => implode('; ', $cookie->getQueuedCookies())], $requestWithCookie->getHeaders());
    }

    public function testRenderIntoSetCookieHeader()
    {
        $cookies = $this->getCreator();
        $cookies->queue($cookies->create('foo', 'bar'));

        $response = $this->mock(ResponseInterface::class);
        $response->shouldReceive('withoutHeader')
            ->with('Set-Cookie')
            ->andReturn($response);
        $response->shouldReceive('withAddedHeader')
            ->with('Set-Cookie', $cookies->getQueuedCookies()['foo']->__toString())
            ->andReturn($response);
        $response->shouldReceive('getHeaders')
            ->andReturn(['Set-Cookie', $cookies->getQueuedCookies()['foo']->__toString()]);

        $responseWithCookie = $cookies->renderIntoSetCookieHeader($response);

        self::assertSame(
            ['Set-Cookie', $cookies->getQueuedCookies()['foo']->__toString()],
            $responseWithCookie->getHeaders()
        );
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }
}
