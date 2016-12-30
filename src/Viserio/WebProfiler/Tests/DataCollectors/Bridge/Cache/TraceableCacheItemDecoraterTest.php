<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TraceableCacheItemDecoraterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetItemMiss()
    {
        $pool = $this->createCachePool();
        $pool->getItem('k');
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('getItem', $call->name);
        static::assertEquals('k', $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(1, $call->misses);
        static::assertNull($call->result);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testGetItemHit()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $pool->getItem('k');
        $calls = $pool->getCalls();

        static::assertCount(3, $calls);

        $call = $calls[2];

        static::assertEquals(1, $call->hits);
        static::assertEquals(0, $call->misses);
    }

    public function testGetItemsMiss()
    {
        $pool  = $this->createCachePool();
        $arg   = ['k0', 'k1'];
        $items = $pool->getItems($arg);

        foreach ($items as $item) {
        }

        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('getItems', $call->name);
        static::assertEquals($arg, $call->argument);
        static::assertEquals(2, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testHasItemMiss()
    {
        $pool = $this->createCachePool();
        $pool->hasItem('k');
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('hasItem', $call->name);
        static::assertEquals('k', $call->argument);
        static::assertFalse($call->result);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testHasItemHit()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $pool->hasItem('k');
        $calls = $pool->getCalls();

        static::assertCount(3, $calls);

        $call = $calls[2];

        static::assertEquals('hasItem', $call->name);
        static::assertEquals('k', $call->argument);
        static::assertTrue($call->result);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testDeleteItem()
    {
        $pool = $this->createCachePool();
        $pool->deleteItem('k');
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('deleteItem', $call->name);
        static::assertEquals('k', $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testDeleteItems()
    {
        $pool = $this->createCachePool();
        $arg  = ['k0', 'k1'];
        $pool->deleteItems($arg);
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('deleteItems', $call->name);
        static::assertEquals($arg, $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testSave()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $calls = $pool->getCalls();

        static::assertCount(2, $calls);

        $call = $calls[1];

        static::assertEquals('save', $call->name);
        static::assertEquals($item, $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testSaveDeferred()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->saveDeferred($item);
        $calls = $pool->getCalls();

        static::assertCount(2, $calls);

        $call = $calls[1];

        static::assertEquals('saveDeferred', $call->name);
        static::assertEquals($item, $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testCommit()
    {
        $pool = $this->createCachePool();
        $pool->commit();
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('commit', $call->name);
        static::assertNull(null, $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testClear()
    {
        $pool = $this->createCachePool();
        $pool->clear();
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertEquals('clear', $call->name);
        static::assertNull(null, $call->argument);
        static::assertEquals(0, $call->hits);
        static::assertEquals(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    private function createCachePool()
    {
        return new TraceableCacheItemDecorater(new ArrayCachePool());
    }
}
