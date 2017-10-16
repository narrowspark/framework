<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\View\Engine;
use Viserio\Component\Contract\View\EngineResolver as EngineResolverContract;
use Viserio\Component\Contract\View\Finder;
use Viserio\Component\Contract\View\View as ViewContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\ViewFactory;

class ViewFactoryTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Contract\View\Factory
     */
    private $viewFactory;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\EngineResolver
     */
    private $engineResolverMock;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\Finder
     */
    private $finderMock;

    /**
     * @var string
     */
    private $path;

    public function setUp(): void
    {
        parent::setUp();

        $this->path               = self::normalizeDirectorySeparator(__DIR__ . '/' . 'Fixture');
        $this->engineResolverMock = $this->mock(EngineResolverContract::class);
        $this->finderMock         = $this->mock(Finder::class);

        $this->viewFactory = new ViewFactory(
            $this->engineResolverMock,
            $this->finderMock
        );
    }

    public function testFileCreatesNewViewInstanceWithProperPathAndEngine(): void
    {
        $this->engineResolverMock->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $this->viewFactory->addExtension('php', 'php');

        $view = $this->viewFactory->file('path.php', ['foo' => 'bar'], ['baz' => 'boom']);

        self::assertSame($engine, $view->getEngine());
    }

    public function testMakeCreatesNewViewInstanceWithProperPathAndEngine(): void
    {
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.php']);
        $this->engineResolverMock->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $this->viewFactory->addExtension('php', 'php');

        $view = $this->viewFactory->create('view', ['foo' => 'bar'], ['baz' => 'boom']);

        self::assertSame($engine, $view->getEngine());
    }

    /**
     * @expectedException \Exception
     */
    public function testExceptionsInSectionsAreThrown(): void
    {
        $this->engineResolverMock->shouldReceive('resolve')
            ->andReturn(new PhpEngine());
        $this->finderMock->shouldReceive('find')
            ->with('layout')
            ->andReturn(['path' => self::normalizeDirectorySeparator($this->path . '/Nested/foo.php')]);
        $this->finderMock->shouldReceive('find')
            ->with('view')
            ->andReturn(['path' => self::normalizeDirectorySeparator($this->path . '/bar/foo/fi.php')]);

        $this->viewFactory->create('view')->render();
    }

    public function testExistsPassesAndFailsViews(): void
    {
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('foo')
            ->andThrow('InvalidArgumentException');
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('bar')
            ->andReturn(['path' => 'path.php']);

        self::assertFalse($this->viewFactory->exists('foo'));
        self::assertTrue($this->viewFactory->exists('bar'));
    }

    public function testRenderEachCreatesViewForEachItemInArray(): void
    {
        $factory = $this->mock(ViewFactory::class . '[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'bar', 'value' => 'baz'])
            ->andReturn($mockView1 = $this->mock(ViewContract::class));
        $factory->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'breeze', 'value' => 'boom'])
            ->andReturn($mockView2 = $this->mock(ViewContract::class));

        $mockView1->shouldReceive('render')
            ->once()
            ->andReturn('dayle');
        $mockView2->shouldReceive('render')
            ->once()
            ->andReturn('rees');

        $result = $factory->renderEach('foo', ['bar' => 'baz', 'breeze' => 'boom'], 'value');

        self::assertEquals('daylerees', $result);
    }

    public function testEmptyViewsCanBeReturnedFromRenderEach(): void
    {
        $factory = $this->mock(ViewFactory::class . '[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo')
            ->andReturn($mockView = $this->mock(ViewContract::class));
        $mockView->shouldReceive('render')
            ->once()
            ->andReturn('empty');

        self::assertEquals('empty', $factory->renderEach('view', [], 'iterator', 'foo'));
    }

    public function testAddANamedViews(): void
    {
        $this->viewFactory->name('bar', 'foo');

        self::assertEquals(['foo' => 'bar'], $this->viewFactory->getNames());
    }

    public function testMakeAViewFromNamedView(): void
    {
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.php']);
        $this->engineResolverMock->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($engine = $this->mock(Engine::class));
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $this->viewFactory->addExtension('php', 'php');
        $this->viewFactory->name('view', 'foo');

        $view = $this->viewFactory->of('foo', ['data']);

        self::assertSame($engine, $view->getEngine());
    }

    public function testEnvironmentAddsExtensionWithCustomResolver(): void
    {
        $resolver = function (): void {
        };
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $this->engineResolverMock->shouldReceive('register')
            ->once()
            ->with('bar', $resolver);
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.foo']);
        $this->engineResolverMock->shouldReceive('resolve')
            ->once()
            ->with('bar')
            ->andReturn($engine = $this->mock(Engine::class));
        $this->viewFactory->addExtension('foo', 'bar', $resolver);

        $view = $this->viewFactory->create('view', ['data']);

        self::assertSame($engine, $view->getEngine());
    }

    public function testAddingExtensionPrependsNotAppends(): void
    {
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $this->viewFactory->addExtension('foo', 'bar');

        $extensions = $this->viewFactory->getExtensions();

        self::assertEquals('bar', \reset($extensions));
        self::assertEquals('foo', \key($extensions));
    }

    public function testPrependedExtensionOverridesExistingExtensions(): void
    {
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('baz');
        $this->viewFactory->addExtension('foo', 'bar');
        $this->viewFactory->addExtension('baz', 'bar');

        $extensions = $this->viewFactory->getExtensions();

        self::assertEquals('bar', \reset($extensions));
        self::assertEquals('baz', \key($extensions));
    }

    public function testMakeWithSlashAndDot(): void
    {
        $this->finderMock->shouldReceive('find')
            ->twice()
            ->with('foo.bar')
            ->andReturn(['path' => 'path.php']);
        $this->engineResolverMock->shouldReceive('resolve')
            ->twice()
            ->with('php')
            ->andReturn($this->mock(Engine::class));

        $this->viewFactory->create('foo/bar');
        $this->viewFactory->create('foo.bar');
    }

    public function testMakeWithAlias(): void
    {
        $this->viewFactory->alias('real', 'alias');
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('real')
            ->andReturn(['path' => 'path.php']);
        $this->engineResolverMock->shouldReceive('resolve')
            ->once()
            ->with('php')
            ->andReturn($this->mock(Engine::class));

        $view = $this->viewFactory->create('alias');

        self::assertEquals('real', $view->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage nrecognized extension in file: [notfound.notfound].
     */
    public function testExceptionIsThrownForUnknownExtension(): void
    {
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('notfound')
            ->andReturn(['path' => 'notfound.notfound', 'name' => 'view', 'extension' => 'notfound']);

        $this->viewFactory->create('notfound');
    }

    public function testGetAnItemFromTheSharedData(): void
    {
        $this->viewFactory->share(['test' => 'foo']);

        self::assertEquals('foo', $this->viewFactory->shared('test'));
    }

    private function getFactoryArgs()
    {
        return [
            $this->mock(EngineResolverContract::class),
            $this->mock(Finder::class),
        ];
    }
}
