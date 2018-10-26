<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Cookie\Cookie as CookieContract;
use Viserio\Component\Cookie\CookieJar;

/**
 * @internal
 */
final class CookieJarTest extends MockeryTestCase
{
    public function testCookiesAreCreatedWithProperOptions(): void
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
        $this->assertNotEquals($c3->getExpiresTime(), Chronos::now()->getTimestamp());
    }

    public function testCookiesAreCreatedWithProperOptionsUsingDefaultPathAndDomain(): void
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

    public function testQueuedCookies(): void
    {
        $cookie = $this->getCreator();
        $this->assertEmpty($cookie->getQueuedCookies());
        $this->assertFalse($cookie->hasQueued('foo'));

        $cookie->queue($cookie->create('foo', 'bar'));
        $this->assertArrayHasKey('foo', $cookie->getQueuedCookies());
        $this->assertTrue($cookie->hasQueued('foo'));
        $this->assertInstanceOf(CookieContract::class, $cookie->queued('foo'));

        $cookie->queue('qu', 'ux');
        $this->assertArrayHasKey('qu', $cookie->getQueuedCookies());
        $this->assertTrue($cookie->hasQueued('qu'));
        $this->assertInstanceOf(CookieContract::class, $cookie->queued('qu'));
    }

    public function testUnqueue(): void
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        $this->assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        $this->assertEmpty($cookie->getQueuedCookies());
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }
}
