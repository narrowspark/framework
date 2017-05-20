<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollectors\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorator;

class TraceableCacheItemDecoratorTest extends TestCase
{
    public function testGetItemMissTrace()
    {
        $pool = $this->createCachePool();
        $pool->getItem('k');
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('getItem', $call->name);
        self::assertSame(['k' => false], $call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(1, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testGetItemHitTrace()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $pool->getItem('k');
        $calls = $pool->getCalls();

        self::assertCount(3, $calls);

        $call = $calls[2];

        self::assertEquals(1, $call->hits);
        self::assertEquals(0, $call->misses);
    }

    public function testGetItemsMissTrace()
    {
        $pool  = $this->createCachePool();
        $arg   = ['k0', 'k1'];
        $items = $pool->getItems($arg);

        foreach ($items as $item) {
        }

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('getItems', $call->name);
        self::assertSame(['k0' => false, 'k1' => false], $call->result);
        self::assertEquals(2, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testHasItemMissTrace()
    {
        $pool = $this->createCachePool();
        $pool->hasItem('k');
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('hasItem', $call->name);
        self::assertSame(['k' => false], $call->result);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testHasItemHitTrace()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $pool->hasItem('k');
        $calls = $pool->getCalls();

        self::assertCount(3, $calls);

        $call = $calls[2];

        self::assertEquals('hasItem', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testDeleteItemTrace()
    {
        $pool = $this->createCachePool();
        $pool->deleteItem('k');
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('deleteItem', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testDeleteItemsTrace()
    {
        $pool = $this->createCachePool();
        $arg  = ['k0', 'k1'];

        $pool->deleteItems($arg);

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('deleteItems', $call->name);
        self::assertTrue($call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testSaveTrace()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->save($item);
        $calls = $pool->getCalls();

        self::assertCount(2, $calls);

        $call = $calls[1];

        self::assertEquals('save', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testSaveDeferredTrace()
    {
        $pool = $this->createCachePool();
        $item = $pool->getItem('k')->set('foo');
        $pool->saveDeferred($item);
        $calls = $pool->getCalls();

        self::assertCount(2, $calls);

        $call = $calls[1];

        self::assertEquals('saveDeferred', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testCommitTrace()
    {
        $pool = $this->createCachePool();
        $pool->commit();

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('commit', $call->name);
        self::assertTrue($call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testClear()
    {
        $pool = $this->createCachePool();
        $pool->clear();

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertEquals('clear', $call->name);
        self::assertNull(null, $call->result);
        self::assertEquals(0, $call->hits);
        self::assertEquals(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    private function createCachePool()
    {
        return new TraceableCacheItemDecorator(new ArrayCachePool());
    }
}
