<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\View\Engine;
use Viserio\Component\Contracts\View\Finder;
use Viserio\Component\Contracts\View\View as ViewContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\Engine\EngineResolver;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\Factory;

class ViewFactoryTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testMakeCreatesNewViewInstanceWithProperPathAndEngine()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.php']);
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

        self::assertSame($engine, $view->getEngine());
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

        self::assertSame($engine, $view->getEngine());
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionsInSectionsAreThrown()
    {
        $factory = $this->getFactory();
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->andReturn(new PhpEngine());
        $factory->getFinder()
            ->shouldReceive('find')
            ->with('layout')
            ->andReturn(['path' => self::normalizeDirectorySeparator($this->getPath() . '/foo.php')]);
        $factory->getFinder()
            ->shouldReceive('find')
            ->with('view')
            ->andReturn(['path' => self::normalizeDirectorySeparator($this->getPath() . '/bar/foo/fi.php')]);

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
            ->andReturn(['path' => 'path.php']);

        self::assertFalse($factory->exists('foo'));
        self::assertTrue($factory->exists('bar'));
    }

    public function testRenderEachCreatesViewForEachItemInArray()
    {
        $factory = $this->mock('Viserio\Component\View\Factory[create]', $this->getFactoryArgs());
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

        self::assertEquals('daylerees', $result);
    }

    public function testEmptyViewsCanBeReturnedFromRenderEach()
    {
        $factory = $this->mock('Viserio\Component\View\Factory[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo')
            ->andReturn($mockView = $this->mock(ViewContract::class));
        $mockView->shouldReceive('render')
            ->once()
            ->andReturn('empty');

        self::assertEquals('empty', $factory->renderEach('view', [], 'iterator', 'foo'));
    }

    public function testAddANamedViews()
    {
        $factory = $this->getFactory();
        $factory->name('bar', 'foo');
        self::assertEquals(['foo' => 'bar'], $factory->getNames());
    }

    public function testMakeAViewFromNamedView()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.php']);
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

        self::assertSame($engine, $view->getEngine());
    }

    public function testEnvironmentAddsExtensionWithCustomResolver()
    {
        $factory  = $this->getFactory();
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
            ->andReturn(['path' => 'path.foo']);
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('bar')
            ->andReturn($engine = $this->mock(Engine::class));
        $factory->addExtension('foo', 'bar', $resolver);

        $view = $factory->create('view', ['data']);

        self::assertSame($engine, $view->getEngine());
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

        self::assertEquals('bar', reset($extensions));
        self::assertEquals('foo', key($extensions));
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

        self::assertEquals('bar', reset($extensions));
        self::assertEquals('baz', key($extensions));
    }

    public function testMakeWithSlashAndDot()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->twice()
            ->with('foo.bar')
            ->andReturn(['path' => 'path.php']);
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
            ->andReturn(['path' => 'path.php']);
        $factory->getEngineResolver()
            ->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($this->mock(Engine::class));

        $view = $factory->create('alias');

        self::assertEquals('real', $view->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownForUnknownExtension()
    {
        $factory = $this->getFactory();
        $factory->getFinder()
            ->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'view.foo']);
        $factory->create('view');
    }

    public function testGetAnItemFromTheSharedData()
    {
        $factory = $this->getFactory();
        $factory->share(['test' => 'foo']);

        self::assertEquals('foo', $factory->shared('test'));
    }

    protected function getFactory()
    {
        return new Factory(
            $this->mock(EngineResolver::class),
            $this->mock(Finder::class)
        );
    }

    protected function getFactoryArgs()
    {
        return [
            $this->mock(EngineResolver::class),
            $this->mock(Finder::class),
        ];
    }

    protected function getPath()
    {
        return self::normalizeDirectorySeparator(__DIR__ . '/' . 'Fixture');
    }
}
