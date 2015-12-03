<?php
namespace Viserio\Cache\Test;

use Mockery as Mock;
use Viserio\Cache\Repository;
use Viserio\Events\Dispatcher;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testGetReturnsValueFromCache()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $this->assertEquals('bar', $repo->get('foo'));
    }

    public function testDefaultValueIsReturned()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $this->assertEquals('bar', $repo->get('foo', 'bar'));
        $this->assertEquals('baz', $repo->get('boom', function () { return 'baz'; }));
    }

    public function testSettingDefaultCacheTime()
    {
        $repo = $this->getRepository();
        $repo->setDefaultCacheTime(10);
        $this->assertEquals(10, $repo->getDefaultCacheTime());
    }

    public function testHasMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn(null);
        $repo->getStore()->shouldReceive('get')->once()->with('bar')->andReturn('bar');
        $this->assertTrue($repo->has('bar'));
        $this->assertFalse($repo->has('foo'));
    }

    public function testRememberMethodCallsPutAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $result = $repo->remember('foo', 10, function () { return 'bar'; });
        $this->assertEquals('bar', $result);
        /*
         * Use Carbon object...
         */
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 9);
        $result = $repo->remember('foo', \Carbon\Carbon::now()->addMinutes(10), function () { return 'bar'; });
        $this->assertEquals('bar', $result);
    }

    public function testRememberForeverMethodCallsForeverAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $result = $repo->rememberForever('foo', function () { return 'bar'; });
        $this->assertEquals('bar', $result);
    }

    protected function getRepository()
    {
        $dispatcher = new Dispatcher(Mock::mock('Symfony\Component\EventDispatcher\EventDispatcher'), Mock::mock('Viserio\Container\Container'));
        $repository = new Repository(Mock::mock('Viserio\Contracts\Cache\Adapter'));
        //$repository->setEventDispatcher($dispatcher);
        return $repository;
    }
}
