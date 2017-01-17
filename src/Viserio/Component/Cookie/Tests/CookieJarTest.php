<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests;

use Cake\Chronos\Chronos;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Component\Cookie\CookieJar;

class CookieJarTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

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
        self::assertInstanceOf(CookieContract::class, $cookie->queued('foo'));

        $cookie->queue('qu', 'ux');
        self::assertArrayHasKey('qu', $cookie->getQueuedCookies());
        self::assertTrue($cookie->hasQueued('qu'));
        self::assertInstanceOf(CookieContract::class, $cookie->queued('qu'));
    }

    public function testUnqueue()
    {
        $cookie = $this->getCreator();
        $cookie->queue($cookie->create('foo', 'bar'));
        self::assertArrayHasKey('foo', $cookie->getQueuedCookies());

        $cookie->unqueue('foo');
        self::assertEmpty($cookie->getQueuedCookies());
    }

    public function getCreator()
    {
        return (new CookieJar())->setDefaultPathAndDomain('/path', '/domain', true);
    }
}
