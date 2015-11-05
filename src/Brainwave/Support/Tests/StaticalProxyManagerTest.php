<?php
namespace Brainwave\Support\Test;

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

use Brainwave\Application\Application;
use Brainwave\Support\StaticalProxyManager;
use Mockery as Mock;

/**
 * StaticalProxyManagerTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class StaticalProxyManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        StaticalProxyManager::clearResolvedInstances();
        FacadeStub::setFacadeApplication(new Application([]));
    }

    public function tearDown()
    {
        Mock::close();
    }

    public function testFacadeCallsUnderlyingApplication()
    {
        $app = new ApplicationStub();
        $app->setAttributes(['foo' => $mock = Mock::mock('StdClass')]);
        $mock->shouldReceive('bar')->once()->andReturn('baz');

        FacadeStub::setFacadeApplication($app);
        $this->assertEquals('baz', FacadeStub::bar());
    }

    public function testShouldReceiveReturnsAMockeryMock()
    {
        $app = new ApplicationStub();
        $app->setAttributes(['foo' => new StdClass()]);

        FacadeStub::setFacadeApplication($app);
        $this->assertInstanceOf('Mockery\MockInterface', $mock = FacadeStub::shouldReceive('foo')->once()->with('bar')->andReturn('baz')->getMock());
        $this->assertEquals('baz', $app['foo']->foo('bar'));
    }

    public function testShouldReceiveCanBeCalledTwice()
    {
        $app = new ApplicationStub();
        $app->setAttributes(['foo' => new StdClass()]);

        FacadeStub::setFacadeApplication($app);
        $this->assertInstanceOf('Mockery\MockInterface', $mock = FacadeStub::shouldReceive('foo')->once()->with('bar')->andReturn('baz')->getMock());
        $this->assertInstanceOf('Mockery\MockInterface', $mock = FacadeStub::shouldReceive('foo2')->once()->with('bar2')->andReturn('baz2')->getMock());
        $this->assertEquals('baz', $app['foo']->foo('bar'));
        $this->assertEquals('baz2', $app['foo']->foo2('bar2'));
    }

    public function testCanBeMockedWithoutUnderlyingInstance()
    {
        FacadeStub::shouldReceive('foo')->once()->andReturn('bar');
        $this->assertEquals('bar', FacadeStub::foo());
    }
}

class FacadeStub extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'foo';
    }
}

class ApplicationStub implements \ArrayAccess
{
    protected $attributes = [];
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
    public function instance($key, $instance)
    {
        $this->attributes[$key] = $instance;
    }
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }
    public function offsetGet($key)
    {
        return $this->attributes[$key];
    }
    public function offsetSet($key, $value)
    {
        $this->attributes[$key] = $value;
    }
    public function offsetUnset($key)
    {
        unset($this->attributes[$key]);
    }
}
