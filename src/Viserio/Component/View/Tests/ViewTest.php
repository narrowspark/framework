<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Support\Arrayable;
use Viserio\Component\Contract\Support\Renderable;
use Viserio\Component\Contract\View\Engine;
use Viserio\Component\View\View;
use Viserio\Component\View\ViewFactory;

/**
 * @internal
 */
final class ViewTest extends MockeryTestCase
{
    public function testDataCanBeSetOnView(): void
    {
        $view = new View(
            $this->mock(ViewFactory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->with('foo', 'bar');
        $view->with(['baz' => 'boom']);

        static::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            $this->mock(ViewFactory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        static::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
    }

    public function testRenderProperlyRendersView(): void
    {
        $view = $this->getView();
        $view->getFactory()
            ->shouldReceive('getShared')
            ->once()
            ->andReturn(['shared' => 'foo']);
        $view->getEngine()
            ->shouldReceive('get')
            ->once()
            ->with(['path' => 'path', 'name' => 'name'], ['foo' => 'bar', 'shared' => 'foo'])
            ->andReturn('contents');

        $me = $this;

        $callback = function (View $rendered, $contents) use ($me, $view): void {
            $me->assertEquals($view, $rendered);
            $me->assertEquals('contents', $contents);
        };

        static::assertEquals('contents', $view->render($callback));
    }

    public function testViewNestBindsASubView(): void
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $view->nest('key', 'foo', ['data']);

        static::assertInstanceOf(View::class, $result);
    }

    public function testViewAcceptsArrayableImplementations(): void
    {
        $arrayable = $this->mock(Arrayable::class);
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);

        $view = new View(
            $this->mock(ViewFactory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            $arrayable
        );

        static::assertEquals('bar', $view->foo);
        static::assertEquals(['qux', 'corge'], $view->baz);
    }

    public function testViewGettersSetters(): void
    {
        $view = $this->getView();

        static::assertEquals($view->getName(), 'view');
        static::assertEquals($view->getPath(), 'path');

        $data = $view->getData();

        static::assertEquals($data['foo'], 'bar');

        $view->setPath('newPath');

        static::assertEquals($view->getPath(), 'newPath');
    }

    public function testViewArrayAccess(): void
    {
        $view = $this->getView();

        static::assertInstanceOf('ArrayAccess', $view);
        static::assertTrue($view->offsetExists('foo'));

        static::assertEquals($view->offsetGet('foo'), 'bar');

        $view->offsetSet('foo', 'baz');

        static::assertEquals($view->offsetGet('foo'), 'baz');

        $view->offsetUnset('foo');

        static::assertFalse($view->offsetExists('foo'));
    }

    public function testViewMagicMethods(): void
    {
        $view = $this->getView();

        static::assertTrue(isset($view->foo));
        static::assertEquals($view->foo, 'bar');

        $view->foo = 'baz';

        static::assertEquals($view->foo, 'baz');
        static::assertEquals($view['foo'], $view->foo);

        unset($view->foo);

        static::assertFalse(isset($view->foo));
        static::assertFalse($view->offsetExists('foo'));
    }

    public function testViewBadMethod(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $view = $this->getView();
        $view->badMethodCall();
    }

    public function testViewGatherDataWithRenderable(): void
    {
        $view = $this->getView();
        $view->getFactory()
            ->shouldReceive('getShared')
            ->twice()
            ->andReturn(['shared' => 'foo']);
        $view->getEngine()
            ->shouldReceive('get')
            ->twice()
            ->andReturn('contents');
        $view->renderable = $this->mock(Renderable::class);
        $view->renderable->shouldReceive('render')
            ->andReturn('text');

        static::assertEquals('contents', $view->render());
        static::assertEquals('contents', (string) $view);
    }

    protected function getView()
    {
        return new View(
            $this->mock(ViewFactory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            ['foo'  => 'bar']
        );
    }
}
