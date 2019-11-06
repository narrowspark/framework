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

use BadMethodCallException;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Assert;
use Viserio\Component\View\View;
use Viserio\Contract\Support\Arrayable;
use Viserio\Contract\Support\Renderable;
use Viserio\Contract\View\Engine;
use Viserio\Contract\View\Factory;

/**
 * @internal
 *
 * @small
 */
final class ViewTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\View\Factory */
    private $viewFactoryMock;

    /** @var \Mockery\MockInterface|\Viserio\Contract\View\Engine */
    private $engineMock;

    /** @var \Viserio\Component\View\View */
    private $view;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->viewFactoryMock = Mockery::mock(Factory::class);
        $this->engineMock = Mockery::mock(Engine::class);

        $this->view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            ['foo' => 'bar']
        );
    }

    public function testDataCanBeSetOnView(): void
    {
        $view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->with('foo', 'bar');
        $view->with(['baz' => 'boom']);

        self::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        self::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
    }

    public function testViewAcceptsArrayableImplementations(): void
    {
        $arrayable = Mockery::mock(Arrayable::class);
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);

        $view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            $arrayable
        );

        self::assertEquals('bar', $view->foo);
        self::assertEquals(['qux', 'corge'], $view->baz);
    }

    public function testRenderProperlyRendersView(): void
    {
        $this->viewFactoryMock
            ->shouldReceive('getShared')
            ->once()
            ->andReturn(['shared' => 'foo']);
        $this->engineMock
            ->shouldReceive('get')
            ->once()
            ->with(['path' => 'path', 'name' => 'name'], ['foo' => 'bar', 'shared' => 'foo'])
            ->andReturn('contents');

        $view = $this->view;

        $callback = static function (View $rendered, $contents) use ($view): void {
            Assert::assertEquals($view, $rendered);
            Assert::assertEquals('contents', $contents);
        };

        self::assertEquals('contents', $this->view->render($callback));
    }

    public function testViewNestBindsASubView(): void
    {
        $this->viewFactoryMock->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $this->view->nest('key', 'foo', ['data']);

        self::assertInstanceOf(View::class, $result);
    }

    public function testViewGettersSetters(): void
    {
        self::assertEquals($this->view->getName(), 'view');
        self::assertEquals($this->view->getPath(), 'path');

        $data = $this->view->getData();

        self::assertEquals($data['foo'], 'bar');

        $this->view->setPath('newPath');

        self::assertEquals($this->view->getPath(), 'newPath');
    }

    public function testViewArrayAccess(): void
    {
        self::assertTrue($this->view->offsetExists('foo'));

        self::assertEquals($this->view->offsetGet('foo'), 'bar');

        $this->view->offsetSet('foo', 'baz');

        self::assertEquals($this->view->offsetGet('foo'), 'baz');

        $this->view->offsetUnset('foo');

        self::assertFalse($this->view->offsetExists('foo'));
    }

    public function testViewMagicMethods(): void
    {
        self::assertTrue(isset($this->view->foo));
        self::assertEquals($this->view->foo, 'bar');

        $this->view->foo = 'baz';

        self::assertEquals($this->view->foo, 'baz');
        self::assertEquals($this->view['foo'], $this->view->foo);

        unset($this->view->foo);

        self::assertFalse(isset($this->view->foo));
        self::assertFalse($this->view->offsetExists('foo'));
    }

    public function testViewBadMethod(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->view->badMethodCall();
    }

    public function testViewGatherDataWithRenderable(): void
    {
        $this->viewFactoryMock
            ->shouldReceive('getShared')
            ->twice()
            ->andReturn(['shared' => 'foo']);
        $this->engineMock
            ->shouldReceive('get')
            ->twice()
            ->andReturn('contents');

        $this->view->renderable = Mockery::mock(Renderable::class);
        $this->view->renderable->shouldReceive('render')
            ->andReturn('text');

        self::assertEquals('contents', $this->view->render());
        self::assertEquals('contents', (string) $this->view);
    }
}
