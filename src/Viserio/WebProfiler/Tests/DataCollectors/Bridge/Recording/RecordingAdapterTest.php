<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors\Bridge\Recording;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Cache\CacheItemInterface;
use Viserio\WebProfiler\DataCollectors\Bridge\Recording\RecordingAdapter;

class RecordingAdapterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetItem()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertInstanceOf(CacheItemInterface::class, $adapter->getItem('test'));
        $object = $adapter->getCalls()[0];

        static::assertFalse($object->isHit);
        static::assertSame('getItem', $object->name);
        static::assertSame(['test'], $object->arguments);
    }

    public function testHasItem()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertFalse($adapter->hasItem('test'));
        $object = $adapter->getCalls()[0];

        static::assertFalse($object->result);
        static::assertSame('hasItem', $object->name);
        static::assertSame(['test'], $object->arguments);
    }

    public function testDeleteItem()
    {
        $adapter = $this->getRecordingAdapter();

        $adapter->deleteItem('test');
        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('deleteItem', $object->name);
        static::assertSame(['test'], $object->arguments);
    }

    public function testSave()
    {
        $adapter = $this->getRecordingAdapter();
        $item    = $this->mock(CacheItemInterface::class);
        $item->shouldReceive('getKey')
            ->twice();
        $item->shouldReceive('get')
            ->once();

        static::assertTrue($adapter->save($item));

        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('save', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    public function testSaveDeferred()
    {
        $adapter = $this->getRecordingAdapter();
        $item    = $this->mock(CacheItemInterface::class);
        $item->shouldReceive('getKey')
            ->times(3);
        $item->shouldReceive('get')
            ->once();

        static::assertTrue($adapter->saveDeferred($item));

        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('saveDeferred', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    public function testGetItems()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertInstanceOf(CacheItemInterface::class, $adapter->getItems(['item'])['item']);

        $object = $adapter->getCalls()[0];

        static::assertTrue(is_string($object->result));
        static::assertSame('getItems', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    public function testClear()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertTrue($adapter->clear());

        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('clear', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    public function testDeleteItems()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertTrue($adapter->deleteItems(['test']));

        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('deleteItems', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    public function testCommit()
    {
        $adapter = $this->getRecordingAdapter();

        static::assertTrue($adapter->commit());

        $object = $adapter->getCalls()[0];

        static::assertTrue($object->result);
        static::assertSame('commit', $object->name);
        static::assertTrue(is_array($object->arguments));
    }

    private function getRecordingAdapter()
    {
        return new RecordingAdapter(new ArrayCachePool());
    }
}
