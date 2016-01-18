<?php
namespace Viserio\Cache\Tests\Adapter;

use Viserio\Cache\Adapter\ArrayCache;

class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testItemsCanBeSetAndRetrieved()
    {
        $store = new ArrayCache();
        $store->put('foo', 'bar', 10);
        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testStoreItemForeverProperlyStoresInArray()
    {
        $mock = $this->getMock('Viserio\Cache\Adapter\ArrayCache', ['put']);
        $mock->expects($this->once())->method('put')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(0));
        $mock->forever('foo', 'bar');
    }

    public function testValuesCanBeIncremented()
    {
        $store = new ArrayCache();
        $store->put('foo', 1, 10);
        $store->increment('foo');
        $this->assertEquals(2, $store->get('foo'));
    }

    public function testMultipleItemsCanBeSetAndRetrieved()
    {
        $store = new ArrayCache();
        $store->put('foo', 'bar', 10);
        $store->putMultiple([
            'foobuu'  => 'buz',
            'quz'   => 'baz',
        ], 10);

        $this->assertEquals([
            'foo'   => 'bar',
            'foobuu'  => 'buz',
            'quz'   => 'baz',
            'foobar'  => null,
        ], $store->getMultiple(['foo', 'foobuu', 'quz', 'foobar']));
    }

    public function testValuesCanBeDecremented()
    {
        $store = new ArrayCache();
        $store->put('foo', 1, 10);
        $store->decrement('foo');
        $this->assertEquals(0, $store->get('foo'));
    }

    public function testItemsCanBeRemoved()
    {
        $store = new ArrayCache();
        $store->put('foo', 'bar', 10);
        $store->forget('foo');
        $this->assertNull($store->get('foo'));
    }

    public function testItemsCanBeFlushed()
    {
        $store = new ArrayCache();
        $store->put('foo', 'bar', 10);
        $store->put('baz', 'boom', 10);
        $store->flush();
        $this->assertNull($store->get('foo'));
        $this->assertNull($store->get('baz'));
    }

    public function testCacheKey()
    {
        $store = new ArrayCache();
        $this->assertEquals('', $store->getPrefix());
    }
}
