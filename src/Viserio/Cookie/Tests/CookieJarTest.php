<?php
declare(strict_types=1);
namespace Viserio\Cookie\Tests;

use Cake\Chronos\Chronos;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cookie\CookieJar;

class CookieJarTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

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

        $c3 = $cookie->delete('color');
        $this->assertNull($c3->getValue());
        $this->assertNotEquals($c3->getExpiresTime()->getTimestamp(), Chronos::now()->getTimestamp());
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
        $this->assertInstanceOf('Viserio\Contracts\Cookie\Cookie', $cookie->queued('qu'));
    }

    public function testUnqueue()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        $this->assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        $this->assertEmpty($cookie->getQueuedCookies());
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

        $this->assertSame(['Cookie' => implode('; ', $cookie->getQueuedCookies())], $requestWithCookie->getHeaders());
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

        $this->assertSame(
            ['Set-Cookie', $cookies->getQueuedCookies()['foo']->__toString()],
            $responseWithCookie->getHeaders()
        );
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }
}
