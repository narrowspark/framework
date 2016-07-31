<?php
declare(strict_types=1);
namespace Viserio\View\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Events\Dispatcher as EventDispatcher;
use Viserio\Contracts\View\{
    Engine,
    Finder,
    View as ViewContract
};
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\View\{
    Engines\Adapter\Php,
    Engines\EngineResolver,
    Factory,
    Virtuoso
};

class ViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    public function testMakeCreatesNewViewInstanceWithProperPathVirtuosoAndEngine()
    {
        unset($_SERVER['__test.view']);

        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn('path.php');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $virtuoso = new Virtuoso(
            $this->mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->getVirtuoso()->creator('view', function ($view) {
            $_SERVER['__test.view'] = $view;
        });

        $view = $factory->create('view', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
        $this->assertSame($_SERVER['__test.view'], $view);

        unset($_SERVER['__test.view']);
    }

    public function testFileCreatesNewViewInstanceWithProperPathVirtuosoAndEngine()
    {
        unset($_SERVER['__test.view']);

        $factory = $this->getFactory();
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $virtuoso = new Virtuoso(
            $this->mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->getVirtuoso()->creator('path.php', function ($view) {
            $_SERVER['__test.view'] = $view;
        });

        $view = $factory->file('path.php', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
        $this->assertSame($_SERVER['__test.view'], $view);

        unset($_SERVER['__test.view']);
    }

    public function testMakeCreatesNewViewInstanceWithProperPathAndEngine()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn('path.php');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $view = $factory->create('view', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
    }

    public function testFileCreatesNewViewInstanceWithProperPathAndEngine()
    {
        $factory = $this->getFactory();
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $view = $factory->file('path.php', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionsInSectionsAreThrown()
    {
        $factory = $this->getFactory();
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->andReturn(new Php());
        $factory->getFinder()
            ->shouldReceive('find')
            ->with('layout')
            ->andReturn(self::normalizeDirectorySeparator($this->getPath() . '/foo.php'));
        $factory->getFinder()
            ->shouldReceive('find')
            ->with('view')
            ->andReturn(self::normalizeDirectorySeparator($this->getPath() . '/bar/foo/fi.php'));

        $virtuoso = new Virtuoso(
            $this->mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->create('view')->render();
    }

    public function testExistsPassesAndFailsViews()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('foo')
            ->andThrow('InvalidArgumentException');
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('bar')
            ->andReturn('path.php');

        $this->assertFalse($factory->exists('foo'));
        $this->assertTrue($factory->exists('bar'));
    }

    public function testRenderEachCreatesViewForEachItemInArray()
    {
        $factory = $this->mock('Viserio\View\Factory[create]', $this->getFactoryArgs());
        $factory
            ->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'bar', 'value' => 'baz'])
            ->andReturn($mockView1 = $this->mock(ViewContract::class));
        $factory
            ->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'breeze', 'value' => 'boom'])
            ->andReturn($mockView2 = $this->mock(ViewContract::class));

        $mockView1
            ->shouldReceive('render')
            ->once()
            ->andReturn('dayle');
        $mockView2
            ->shouldReceive('render')
            ->once()
            ->andReturn('rees');

        $result = $factory->renderEach('foo', ['bar' => 'baz', 'breeze' => 'boom'], 'value');

        $this->assertEquals('daylerees', $result);
    }

    public function testEmptyViewsCanBeReturnedFromRenderEach()
    {
        $factory = $this->mock('Viserio\View\Factory[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo')
            ->andReturn($mockView = $this->mock(ViewContract::class));
        $mockView->shouldReceive('render')
            ->once()
            ->andReturn('empty');

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
        $factory->getFinder()
            ->shouldReceive('find')->once()->with('view')->andReturn('path.php');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
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
        $resolver = function () {
        };
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $factory->getEngineResolver()
            ->shouldReceive('register')
            ->once()
            ->with('bar', $resolver);
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn('path.foo');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('bar')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->addExtension('foo', 'bar', $resolver);

        $view = $factory->create('view', ['data']);

        $this->assertSame($engine, $view->getEngine());
    }

    public function testAddingExtensionPrependsNotAppends()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $factory->addExtension('foo', 'bar');

        $extensions = $factory->getExtensions();

        $this->assertEquals('bar', reset($extensions));
        $this->assertEquals('foo', key($extensions));
    }

    public function testPrependedExtensionOverridesExistingExtensions()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('baz');
        $factory->addExtension('foo', 'bar');
        $factory->addExtension('baz', 'bar');

        $extensions = $factory->getExtensions();

        $this->assertEquals('bar', reset($extensions));
        $this->assertEquals('baz', key($extensions));
    }

    public function testMakeWithSlashAndDot()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->twice()
            ->with('foo.bar')
            ->andReturn('path.php');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->twice()
            ->with('php')
            ->andReturn($this->mock(Engine::class));
        $factory->create('foo/bar');
        $factory->create('foo.bar');
    }

    public function testMakeWithAlias()
    {
        $factory = $this->getFactory();
        $factory->alias('real', 'alias');
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('real')
            ->andReturn('path.php');
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($this->mock(Engine::class));

        $view = $factory->create('alias');

        $this->assertEquals('real', $view->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionIsThrownForUnknownExtension()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn('view.foo');
        $factory->create('view');
    }

    public function testGetAnItemFromTheSharedData()
    {
        $factory = $this->getFactory();
        $factory->share(['test' => 'foo']);

        $this->assertEquals('foo', $factory->shared('test'));
    }

    protected function getFactory()
    {
        return new Factory(
            $this->mock(EngineResolver::class),
            $this->mock(Finder::class),
            new EventDispatcher($this->mock(ContainerInterface::class))
        );
    }

    protected function getFactoryArgs()
    {
        return [
            $this->mock(EngineResolver::class),
            $this->mock(Finder::class),
            new EventDispatcher($this->mock(ContainerInterface::class)),
        ];
    }

    protected function getPath()
    {
        return self::normalizeDirectorySeparator(dirname(__FILE__) . '/' . 'Fixture');
    }
}
