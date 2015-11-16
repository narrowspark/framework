<?php
namespace Viserio\Cache\Test;

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

use Viserio\Cache\Repository;
use Viserio\Events\Dispatcher;
use Mockery as Mock;

/**
 * RedisCacheTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
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
