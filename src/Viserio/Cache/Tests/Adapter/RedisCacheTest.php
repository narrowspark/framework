<?php
namespace Viserio\Cache\Test\Adapter;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Mockery as Mock;
use Viserio\Cache\Adapter\RedisCache;

/**
 * RedisCacheTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class RedisCacheTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testGetReturnsNullWhenNotFound()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('get')->once()->with('prefix:foo')->andReturn(null);
        $this->assertNull($redis->get('foo'));
    }

    public function testRedisValueIsReturned()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('get')->once()->with('prefix:foo')->andReturn(serialize('foo'));
        $this->assertEquals('foo', $redis->get('foo'));
    }

    public function testRedisMultipleValuesAreReturned()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('mget')->once()->with(['prefix:foo', 'prefix:fuuboo', 'prefix:fiboo'])
            ->andReturn([
                serialize('bar'),
                serialize('buzz'),
                serialize('quz')
            ]);

        $this->assertEquals([
            'foo'   => 'bar',
            'fuuboo'  => 'buzz',
            'fiboo'  => 'quz'
        ], $redis->getMultiple([
            'foo', 'fuuboo', 'fiboo'
        ]));
    }

    public function testRedisValueIsReturnedForNumerics()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('get')->once()->with('prefix:foo')->andReturn(1);
        $this->assertEquals(1, $redis->get('foo'));
    }

    public function testSetMultipleMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        /** @var Mock\MockInterface $connection */
        $connection = $redis->getRedis();
        $connection->shouldReceive('connection')->with('default')->andReturn($redis->getRedis());
        $connection->shouldReceive('multi')->once();
        $redis->getRedis()->shouldReceive('setex')->with('prefix:foo', 60 * 60, serialize('bar'));
        $redis->getRedis()->shouldReceive('setex')->with('prefix:baz', 60 * 60, serialize('qux'));
        $redis->getRedis()->shouldReceive('setex')->with('prefix:bar', 60 * 60, serialize('fiibuu'));
        $connection->shouldReceive('exec')->once();

        $redis->putMultiple([
            'foo' => 'bar',
            'baz' => 'qux',
            'bar' => 'fiibuu'
        ], 60);
    }

    public function testSetMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('setex')->once()->with('prefix:foo', 60 * 60, serialize('foo'));
        $redis->put('foo', 'foo', 60);
    }

    public function testSetMethodProperlyCallsRedisForNumerics()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('setex')->once()->with('prefix:foo', 60 * 60, 1);
        $redis->put('foo', 1, 60);
    }

    public function testIncrementMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('incrby')->once()->with('prefix:foo', 5);
        $redis->increment('foo', 5);
    }

    public function testDecrementMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('decrby')->once()->with('prefix:foo', 5);
        $redis->decrement('foo', 5);
    }

    public function testStoreItemForeverProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('set')->once()->with('prefix:foo', serialize('foo'));
        $redis->forever('foo', 'foo', 60);
    }

    public function testForgetMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('del')->once()->with('prefix:foo');
        $redis->forget('foo');
    }

    protected function getRedis()
    {
        $mock = Mock::mock('Viserio\Cache\Adapter\RedisCache');

        return new RedisCache($mock::connect(null, []), 'prefix');
    }
}
