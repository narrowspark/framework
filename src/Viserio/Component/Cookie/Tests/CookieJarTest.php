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
        static::assertEquals('blue', $c->getValue());
        static::assertFalse($c->isHttpOnly());
        static::assertTrue($c->isSecure());
        static::assertEquals('/domain', $c->getDomain());
        static::assertEquals('/path', $c->getPath());

        $c2 = $cookie->forever('color', 'blue', '/path', '/domain', true, false);
        static::assertEquals('blue', $c2->getValue());
        static::assertFalse($c2->isHttpOnly());
        static::assertTrue($c2->isSecure());
        static::assertEquals('/domain', $c2->getDomain());
        static::assertEquals('/path', $c2->getPath());

        $c3 = $cookie->delete('color');
        static::assertNull($c3->getValue());
        static::assertNotEquals($c3->getExpiresTime(), Chronos::now()->getTimestamp());
    }

    public function testCookiesAreCreatedWithProperOptionsUsingDefaultPathAndDomain(): void
    {
        $cookie = $this->getCreator();
        $cookie->setDefaultPathAndDomain('/path', '/domain');

        $c = $cookie->create('color', 'blue', 10, null, null, true, false);
        static::assertEquals('blue', $c->getValue());
        static::assertFalse($c->isHttpOnly());
        static::assertTrue($c->isSecure());
        static::assertEquals('/domain', $c->getDomain());
        static::assertEquals('/path', $c->getPath());
    }

    public function testQueuedCookies(): void
    {
        $cookie = $this->getCreator();
        static::assertEmpty($cookie->getQueuedCookies());
        static::assertFalse($cookie->hasQueued('foo'));

        $cookie->queue($cookie->create('foo', 'bar'));
        static::assertArrayHasKey('foo', $cookie->getQueuedCookies());
        static::assertTrue($cookie->hasQueued('foo'));
        static::assertInstanceOf(CookieContract::class, $cookie->queued('foo'));

        $cookie->queue('qu', 'ux');
        static::assertArrayHasKey('qu', $cookie->getQueuedCookies());
        static::assertTrue($cookie->hasQueued('qu'));
        static::assertInstanceOf(CookieContract::class, $cookie->queued('qu'));
    }

    public function testUnqueue(): void
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        static::assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        static::assertEmpty($cookie->getQueuedCookies());
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }
}
