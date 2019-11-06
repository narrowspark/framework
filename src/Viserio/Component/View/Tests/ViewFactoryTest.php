<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\View\Tests;

use Exception;
use InvalidArgumentException;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\View\Engine\FileEngine;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\ViewFactory;
use Viserio\Contract\View\Engine;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;
use Viserio\Contract\View\Finder;
use Viserio\Contract\View\View as ViewContract;

/**
 * @internal
 *
 * @small
 */
final class ViewFactoryTest extends MockeryTestCase
{
    /** @var \Viserio\Contract\View\Factory */
    private $viewFactory;

    /** @var \Mockery\MockInterface|\Viserio\Contract\View\EngineResolver */
    private $engineResolverMock;

    /** @var \Mockery\MockInterface|\Viserio\Contract\View\Finder */
    private $finderMock;

    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';
        $this->engineResolverMock = Mockery::mock(EngineResolverContract::class);
        $this->finderMock = Mockery::mock(Finder::class);

        $this->viewFactory = new ViewFactory(
            $this->engineResolverMock,
            $this->finderMock
        );
    }

    public function testFileCreatesNewViewInstanceWithProperPathAndEngine(): void
    {
        $this->engineResolverMock->shouldReceive('get')
            ->once()
            ->with('php')
            ->andReturn($engine = Mockery::mock(Engine::class));
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
        $this->engineResolverMock->shouldReceive('get')
            ->once()
            ->with('php')
            ->andReturn($engine = Mockery::mock(Engine::class));
        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('php');
        $this->viewFactory->addExtension('php', 'php');

        $view = $this->viewFactory->create('view', ['foo' => 'bar'], ['baz' => 'boom']);

        self::assertSame($engine, $view->getEngine());
    }

    public function testExceptionsInSectionsAreThrown(): void
    {
        $this->expectException(Exception::class);

        $this->engineResolverMock->shouldReceive('get')
            ->andReturn(new PhpEngine());
        $this->finderMock->shouldReceive('find')
            ->with('layout')
            ->andReturn(['path' => $this->path . \DIRECTORY_SEPARATOR . 'Nested' . \DIRECTORY_SEPARATOR . 'foo.php']);
        $this->finderMock->shouldReceive('find')
            ->with('view')
            ->andReturn(['path' => $this->path . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'fi.php']);

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
        $factory = Mockery::mock(ViewFactory::class . '[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'bar', 'value' => 'baz'])
            ->andReturn($mockView1 = Mockery::mock(ViewContract::class));
        $factory->shouldReceive('create')
            ->once()
            ->with('foo', ['key' => 'breeze', 'value' => 'boom'])
            ->andReturn($mockView2 = Mockery::mock(ViewContract::class));

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
        $factory = Mockery::mock(ViewFactory::class . '[create]', $this->getFactoryArgs());
        $factory->shouldReceive('create')
            ->once()
            ->with('foo')
            ->andReturn($mockView = Mockery::mock(ViewContract::class));
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
        $this->engineResolverMock->shouldReceive('get')
            ->once()
            ->with('php')
            ->andReturn($engine = Mockery::mock(Engine::class));
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
        $fileEngine = new FileEngine();

        $this->finderMock->shouldReceive('addExtension')
            ->once()
            ->with('foo');
        $this->engineResolverMock->shouldReceive('set')
            ->once()
            ->with('bar', $fileEngine);
        $this->finderMock->shouldReceive('find')
            ->once()
            ->with('view')
            ->andReturn(['path' => 'path.foo']);
        $this->engineResolverMock->shouldReceive('get')
            ->once()
            ->with('bar')
            ->andReturn($engine = Mockery::mock(Engine::class));
        $this->viewFactory->addExtension('foo', 'bar', $fileEngine);

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
        $this->engineResolverMock->shouldReceive('get')
            ->twice()
            ->with('php')
            ->andReturn(Mockery::mock(Engine::class));

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
        $this->engineResolverMock->shouldReceive('get')
            ->once()
            ->with('php')
            ->andReturn(Mockery::mock(Engine::class));

        $view = $this->viewFactory->create('alias');

        self::assertEquals('real', $view->getName());
    }

    public function testExceptionIsThrownForUnknownExtension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('nrecognized extension in file: [notfound.notfound].');

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

    /**
     * @return array
     */
    private function getFactoryArgs(): array
    {
        return [
            Mockery::mock(EngineResolverContract::class),
            Mockery::mock(Finder::class),
        ];
    }
}
