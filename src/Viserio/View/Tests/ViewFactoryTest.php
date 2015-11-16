<?php
namespace Viserio\View\Test;

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

use Viserio\View\Factory;
use Mockery as Mock;

/**
 * ViewTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class ViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testMakeCreatesNewViewInstanceWithProperPathAndEngine()
    {
        unset($_SERVER['__test.view']);
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('find')->once()->with('view')->andReturn('path.php');
        $factory->getEngineResolver()->shouldReceive('resolve')->once()->with('php')->andReturn($engine = Mock::mock('Viserio\Contracts\View\Engine'));
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('php');
        $factory->addExtension('php', 'php');
        $view = $factory->make('view', ['foo' => 'bar'], ['baz' => 'boom']);
        $this->assertSame($engine, $view->getEngine());
        $this->assertSame($_SERVER['__test.view'], $view);
        unset($_SERVER['__test.view']);
    }

    public function testExistsPassesAndFailsViews()
    {
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('find')->once()->with('foo')->andThrow('InvalidArgumentException');
        $factory->getFinder()->shouldReceive('find')->once()->with('bar')->andReturn('path.php');
        $this->assertFalse($factory->exists('foo'));
        $this->assertTrue($factory->exists('bar'));
    }

    public function testRenderEachCreatesViewForEachItemInArray()
    {
        $factory = Mock::mock('Viserio\View\Factory[make]', $this->getFactoryArgs());
        $factory->shouldReceive('make')->once()->with('foo', ['key' => 'bar', 'value' => 'baz'])->andReturn($mockView1 = Mock::mock('StdClass'));
        $factory->shouldReceive('make')->once()->with('foo', ['key' => 'breeze', 'value' => 'boom'])->andReturn($mockView2 = Mock::mock('StdClass'));
        $mockView1->shouldReceive('render')->once()->andReturn('dayle');
        $mockView2->shouldReceive('render')->once()->andReturn('rees');
        $result = $factory->renderEach('foo', ['bar' => 'baz', 'breeze' => 'boom'], 'value');
        $this->assertEquals('daylerees', $result);
    }

    public function testEmptyViewsCanBeReturnedFromRenderEach()
    {
        $factory = Mock::mock('Viserio\View\Factory[make]', $this->getFactoryArgs());
        $factory->shouldReceive('make')->once()->with('foo')->andReturn($mockView = Mock::mock('StdClass'));
        $mockView->shouldReceive('render')->once()->andReturn('empty');
        $this->assertEquals('empty', $factory->renderEach('view', [], 'iterator', 'foo'));
    }

    public function testAddANamedViews()
    {
        $factory = $this->getFactory();
        $factory->name('bar', 'foo');
        $this->assertEquals(['foo' => 'bar'], $factory->getNames());
    }

    public function testMakeAViewFromNamedView()
    {
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('find')->once()->with('view')->andReturn('path.php');
        $factory->getEngineResolver()->shouldReceive('resolve')->once()->with('php')->andReturn($engine = Mock::mock('Viserio\Contracts\View\Engine'));
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('php');
        $factory->addExtension('php', 'php');
        $factory->name('view', 'foo');
        $view = $factory->of('foo', ['data']);
        $this->assertSame($engine, $view->getEngine());
    }

    public function testRawStringsMayBeReturnedFromRenderEach()
    {
        $this->assertEquals('foo', $this->getFactory()->renderEach('foo', [], 'item', 'raw|foo'));
    }

    public function testEnvironmentAddsExtensionWithCustomResolver()
    {
        $factory = $this->getFactory();
        $resolver = function () {};
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('foo');
        $factory->getEngineResolver()->shouldReceive('register')->once()->with('bar', $resolver);
        $factory->getFinder()->shouldReceive('find')->once()->with('view')->andReturn('path.foo');
        $factory->getEngineResolver()->shouldReceive('resolve')->once()->with('bar')->andReturn($engine = Mock::mock('Viserio\Contracts\View\Engine'));
        $factory->addExtension('foo', 'bar', $resolver);
        $view = $factory->make('view', ['data']);
        $this->assertSame($engine, $view->getEngine());
    }

    public function testAddingExtensionPrependsNotAppends()
    {
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('foo');
        $factory->addExtension('foo', 'bar');
        $extensions = $factory->getExtensions();
        $this->assertEquals('bar', reset($extensions));
        $this->assertEquals('foo', key($extensions));
    }

    public function testPrependedExtensionOverridesExistingExtensions()
    {
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('foo');
        $factory->getFinder()->shouldReceive('addExtension')->once()->with('baz');
        $factory->addExtension('foo', 'bar');
        $factory->addExtension('baz', 'bar');
        $extensions = $factory->getExtensions();
        $this->assertEquals('bar', reset($extensions));
        $this->assertEquals('baz', key($extensions));
    }

    public function testMakeWithSlashAndDot()
    {
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('find')->twice()->with('foo.bar')->andReturn('path.php');
        $factory->getEngineResolver()->shouldReceive('resolve')->twice()->with('php')->andReturn(Mock::mock('Viserio\Contracts\View\Engine'));
        $factory->make('foo/bar');
        $factory->make('foo.bar');
    }

    public function testMakeWithAlias()
    {
        $factory = $this->getFactory();
        $factory->alias('real', 'alias');
        $factory->getFinder()->shouldReceive('find')->once()->with('real')->andReturn('path.php');
        $factory->getEngineResolver()->shouldReceive('resolve')->once()->with('php')->andReturn(Mock::mock('Viserio\Contracts\View\Engine'));
        $view = $factory->make('alias');
        $this->assertEquals('real', $view->getName());
    }

    public function testExceptionIsThrownForUnknownExtension()
    {
        $this->setExpectedException('InvalidArgumentException');
        $factory = $this->getFactory();
        $factory->getFinder()->shouldReceive('find')->once()->with('view')->andReturn('view.foo');
        $factory->make('view');
    }

    protected function getFactory()
    {
        return new Factory($this->getFactoryArgs());
    }

    protected function getFactoryArgs()
    {
        return [
            Mock::mock('Viserio\View\Engines\EngineResolver'),
            Mock::mock('Viserio\Contracts\View\Finder'),
            Mock::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
        ];
    }
}
