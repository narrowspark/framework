<?php
namespace Viserio\View\Test;

use Interop\Container\ContainerInterface;
use Mockery as Mock;
use StdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Viserio\Contracts\View\Engine;
use Viserio\Contracts\View\Finder;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\View\Engines\Adapter\Php;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\Virtuoso;

class ViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function tearDown()
    {
        Mock::close();
    }

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
            ->andReturn($engine = Mock::mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $virtuoso = new Virtuoso(
            Mock::mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->getVirtuoso()->creator('view', function ($view) {
            $_SERVER['__test.view'] = $view;
        });

        $view = $factory->make('view', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
        $this->assertSame($_SERVER['__test.view']->getSubject(), $view);

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
            ->andReturn($engine = Mock::mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $virtuoso = new Virtuoso(
            Mock::mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->getVirtuoso()->creator('path.php', function ($view) {
            $_SERVER['__test.view'] = $view;
        });

        $view = $factory->file('path.php', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
        $this->assertSame($_SERVER['__test.view']->getSubject(), $view);

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
            ->andReturn($engine = Mock::mock(Engine::class));
        $factory->getFinder()
            ->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $factory->addExtension('php', 'php');

        $view = $factory->make('view', ['foo' => 'bar'], ['baz' => 'boom']);

        $this->assertSame($engine, $view->getEngine());
    }

    public function testFileCreatesNewViewInstanceWithProperPathAndEngine()
    {
        $factory = $this->getFactory();
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = Mock::mock(Engine::class));
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
            ->andReturn($this->normalizeDirectorySeparator($this->getPath() . '/foo.php'));
        $factory->getFinder()
            ->shouldReceive('find')
            ->with('view')
            ->andReturn($this->normalizeDirectorySeparator($this->getPath() . '/bar/foo/fi.php'));

        $virtuoso = new Virtuoso(
            Mock::mock(ContainerInterface::class),
            $factory->getDispatcher()
        );

        $factory->setVirtuoso($virtuoso);

        $factory->make('view')->render();
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
        $factory = Mock::mock('Viserio\View\Factory[make]', $this->getFactoryArgs());
        $factory->shouldReceive('make')
            ->once()
            ->with('foo', ['key' => 'bar', 'value' => 'baz'])
            ->andReturn($mockView1 = Mock::mock(StdClass::class));
        $factory->shouldReceive('make')
            ->once()
            ->with('foo', ['key' => 'breeze', 'value' => 'boom'])
            ->andReturn($mockView2 = Mock::mock(StdClass::class));

        $mockView1->shouldReceive('render')->once()->andReturn('dayle');
        $mockView2->shouldReceive('render')->once()->andReturn('rees');

        $result = $factory->renderEach('foo', ['bar' => 'baz', 'breeze' => 'boom'], 'value');
        $this->assertEquals('daylerees', $result);
    }

    public function testEmptyViewsCanBeReturnedFromRenderEach()
    {
        $factory = Mock::mock('Viserio\View\Factory[make]', $this->getFactoryArgs());
        $factory->shouldReceive('make')
            ->once()
            ->with('foo')
            ->andReturn($mockView = Mock::mock(StdClass::class));
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
            ->andReturn($engine = Mock::mock(Engine::class));
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
            ->andReturn($engine = Mock::mock(Engine::class));
        $factory->addExtension('foo', 'bar', $resolver);

        $view = $factory->make('view', ['data']);

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
            ->andReturn(Mock::mock(Engine::class));
        $factory->make('foo/bar');
        $factory->make('foo.bar');
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
            ->andReturn(Mock::mock(Engine::class));

        $view = $factory->make('alias');

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
        $factory->make('view');
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
            Mock::mock(EngineResolver::class),
            Mock::mock(Finder::class),
            new EventDispatcher()
        );
    }

    protected function getFactoryArgs()
    {
        return [
            Mock::mock(EngineResolver::class),
            Mock::mock(Finder::class),
            new EventDispatcher(),
        ];
   }



    protected function getPath()
    {
        return $this->normalizeDirectorySeparator(dirname(__FILE__) . '/' . 'Fixture');
    }
}
