<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits;

trait SimpleTraceableCacheDecoratorTestTrait
{
    public function testGetMissTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->get('k');
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('get', $call->name);
        self::assertSame(['k' => false], $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(1, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testGetHitTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->set('k', 'foo');
        $pool->get('k');
        $calls = $pool->getCalls();

        self::assertCount(2, $calls);

        $call = $calls[1];

        self::assertSame(1, $call->hits);
        self::assertSame(0, $call->misses);
    }

    public function testGetMultipleMissTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->set('k1', 123);

        $values = $pool->getMultiple(['k0', 'k1']);

        foreach ($values as $value) {
        }

        $calls = $pool->getCalls();

        self::assertCount(2, $calls);

        $call = $calls[1];

        self::assertSame('getMultiple', $call->name);
        self::assertSame(['k0' => false, 'k1' => true], $call->result);
        self::assertSame(1, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testHasMissTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->has('k');

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('has', $call->name);
        self::assertSame(['k' => false], $call->result);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testHasHitTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->set('k', 'foo');
        $pool->has('k');

        $calls = $pool->getCalls();

        self::assertCount(2, $calls);

        $call = $calls[1];

        self::assertSame('has', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testDeleteTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->delete('k');

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('delete', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testDeleteMultipleTrace()
    {
        $pool = $this->createSimpleCache();
        $arg  = ['k0', 'k1'];

        $pool->deleteMultiple($arg);

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('deleteMultiple', $call->name);
        self::assertSame(['keys' => $arg, 'result' => true], $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testTraceSetTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->set('k', 'foo');

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('set', $call->name);
        self::assertSame(['k' => true], $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testSetMultipleTrace()
    {
        $pool = $this->createSimpleCache();
        $pool->setMultiple(['k' => 'foo']);

        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('setMultiple', $call->name);
        self::assertSame(['keys' => ['k'], 'result' => true], $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    abstract protected function createSimpleCache();
}
