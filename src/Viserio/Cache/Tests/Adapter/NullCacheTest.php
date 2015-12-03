<?php
namespace Viserio\Cache\Tests\Adapter;

use Viserio\Cache\Adapter\NullCache;

class NullCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testItemsCanNotBeCached()
    {
        $store = new NullCache();
        $store->put('foo', 'bar', 10);
        $this->assertNull($store->get('foo'));
    }

    public function testGetMultipleReturnsMultipleNulls()
    {
        $store = new NullCache;

        $this->assertEquals([
            'foo'   => null,
            'bar'   => null,
        ], $store->getMultiple([
            'foo',
            'bar',
        ]));
    }
}
