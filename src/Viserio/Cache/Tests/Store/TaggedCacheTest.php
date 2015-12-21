<?php
namespace Viserio\Cache\Test\Store;

use Mockery as Mock;
use Viserio\Cache\Adapter\ArrayCache;
use Viserio\Cache\Adapter\RedisTaggedCache;

class TaggedCacheTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testSectionCanBeFlushed()
    {
        $store = new ArrayCache();
        $store->section('bop')->put('foo', 'bar', 10);
        $store->section('zap')->put('baz', 'boom', 10);
        $store->section('bop')->flush();
        $this->assertNull($store->section('bop')->get('foo'));
        $this->assertEquals('boom', $store->section('zap')->get('baz'));
    }

    public function testCacheCanBeSavedWithMultipleTags()
    {
        $store = new ArrayCache();
        $tags = ['bop', 'zap'];
        $store->tags($tags)->put('foo', 'bar', 10);
        $this->assertEquals('bar', $store->tags($tags)->get('foo'));
    }

    public function testCacheCanBeSetWithDatetimeArgument()
    {
        $store = new ArrayCache();
        $tags = ['bop', 'zap'];
        $duration = new \DateTime();
        $duration->add(new \DateInterval('PT10M'));
        $store->tags($tags)->put('foo', 'bar', $duration);
        $this->assertEquals('bar', $store->tags($tags)->get('foo'));
    }

    public function testCacheSavedWithMultipleTagsCanBeFlushed()
    {
        $store = new ArrayCache();
        $tags1 = ['bop', 'zap'];
        $store->tags($tags1)->put('foo', 'bar', 10);
        $tags2 = ['bam', 'pow'];
        $store->tags($tags2)->put('foo', 'bar', 10);
        $store->tags('zap')->flush();
        $this->assertNull($store->tags($tags1)->get('foo'));
        $this->assertEquals('bar', $store->tags($tags2)->get('foo'));
    }

    public function testTagsWithStringArgument()
    {
        $store = new ArrayCache();
        $store->tags('bop')->put('foo', 'bar', 10);
        $this->assertEquals('bar', $store->tags('bop')->get('foo'));
    }

    public function testTagsCacheForever()
    {
        $store = new ArrayCache();
        $tags = ['bop', 'zap'];
        $store->tags($tags)->forever('foo', 'bar');
        $this->assertEquals('bar', $store->tags($tags)->get('foo'));
    }

    public function testRedisCacheTagsPushForeverKeysCorrectly()
    {
        $store = Mock::mock('Viserio\Contracts\Cache\Store');
        $tagSet = Mock::mock('Viserio\Cache\Store\TagSet', [$store, ['foo', 'bar']]);
        $tagSet->shouldReceive('getNamespace')->andReturn('foo|bar');

        $redis = new RedisTaggedCache($store, $tagSet);
        $store->shouldReceive('getPrefix')->andReturn('prefix:');
        $store->shouldReceive('connection')->andReturn($conn = Mock::mock('StdClass'));
        $conn->shouldReceive('lpush')->once()->with('prefix:foo:forever', 'prefix:' . sha1('foo|bar') . ':key1');
        $conn->shouldReceive('lpush')->once()->with('prefix:bar:forever', 'prefix:' . sha1('foo|bar') . ':key1');
        $store->shouldReceive('forever')->with(sha1('foo|bar') . ':key1', 'key1:value');
        $redis->forever('key1', 'key1:value');
    }

    public function testRedisCacheForeverTagsCanBeFlushed()
    {
        $store = Mock::mock('Viserio\Contracts\Cache\Store');
        $tagSet = Mock::mock('Viserio\Cache\Store\TagSet', [$store, ['foo', 'bar']]);
        $tagSet->shouldReceive('getNamespace')->andReturn('foo|bar');

        $redis = new RedisTaggedCache($store, $tagSet);
        $store->shouldReceive('getPrefix')->andReturn('prefix:');
        $store->shouldReceive('connection')->andReturn($conn = Mock::mock('StdClass'));
        $conn->shouldReceive('lrange')->once()->with('prefix:foo:forever', 0, -1)->andReturn(['key1', 'key2']);
        $conn->shouldReceive('lrange')->once()->with('prefix:bar:forever', 0, -1)->andReturn(['key3']);
        $conn->shouldReceive('del')->once()->with('key1', 'key2');
        $conn->shouldReceive('del')->once()->with('key3');
        $conn->shouldReceive('del')->once()->with('prefix:foo:forever');
        $conn->shouldReceive('del')->once()->with('prefix:bar:forever');
        $tagSet->shouldReceive('reset')->once();
        $redis->flush();
    }
}
