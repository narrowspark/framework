<?php

namespace Brainwave\Cache\Test\Adapter;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Cache\Adapter\MemcachedCache;
use Mockery as Mock;

/**
 * MemcachedCacheTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class MemcachedCacheTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testGetReturnsNullWhenNotFound()
    {
        $memcache = $this->getMock('StdClass', ['get', 'getResultCode']);
        $memcache->expects($this->once())->method('get')->with($this->equalTo('foo:bar'))->will($this->returnValue(null));
        $memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(1));
        $store = new MemcachedCache($memcache, 'foo');
        $this->assertNull($store->get('bar'));
    }

    public function testMemcacheValueIsReturned()
    {
        $memcache = $this->getMock('StdClass', ['get', 'getResultCode']);
        $memcache->expects($this->once())->method('get')->will($this->returnValue('bar'));
        $memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(0));
        $store = new MemcachedCache($memcache);
        $this->assertEquals('bar', $store->get('foo'));
    }

    public function testSetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['set']);
        $memcache->expects($this->once())->method('set')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(60));
        $store = new MemcachedCache($memcache);
        $store->put('foo', 'bar', 1);
    }

    public function testIncrementMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['increment']);
        $memcache->expects($this->once())->method('increment')->with($this->equalTo('foo'), $this->equalTo(5));
        $store = new MemcachedCache($memcache);
        $store->increment('foo', 5);
    }

    public function testDecrementMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['decrement']);
        $memcache->expects($this->once())->method('decrement')->with($this->equalTo('foo'), $this->equalTo(5));
        $store = new MemcachedCache($memcache);
        $store->decrement('foo', 5);
    }

    public function testStoreItemForeverProperlyCallsMemcached()
    {
        $memcache = $this->getMock('Memcached', ['set']);
        $memcache->expects($this->once())->method('set')->with($this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(0));
        $store = new MemcachedCache($memcache);
        $store->forever('foo', 'bar');
    }

    public function testForgetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['delete']);
        $memcache->expects($this->once())->method('delete')->with($this->equalTo('foo'));
        $store = new MemcachedCache($memcache);
        $store->forget('foo');
    }

    public function testServersAreAddedCorrectly()
    {
        $connector = $this->getMock('Brainwave\Cache\Adapter\MemcachedCache', ['getMemcached']);

        $memcached = Mock::mock('stdClass');

        $memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
        $memcached->shouldReceive('getVersion')->once()->andReturn(true);
        $connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));
        $result = $connector->connect([['host' => 'localhost', 'port' => 11211, 'weight' => 100]]);
        $this->assertSame($result, $memcached);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testExceptionThrownOnBadConnection()
    {
        $connector = $this->getMock('Brainwave\Cache\Adapter\MemcachedCache', ['getMemcached']);

        $memcached = Mock::mock('stdClass');

        $memcached->shouldReceive('addServer')->once()->with('localhost', 11211, 100);
        $memcached->shouldReceive('getVersion')->once()->andReturn(false);
        $connector->expects($this->once())->method('getMemcached')->will($this->returnValue($memcached));
        $result = $connector->connect([['host' => 'localhost', 'port' => 11211, 'weight' => 100]]);
    }
}
