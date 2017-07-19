<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Support\Arrayable;
use Viserio\Component\Contracts\Support\Renderable;
use Viserio\Component\Contracts\View\Engine;
use Viserio\Component\View\Factory;
use Viserio\Component\View\View;

class ViewTest extends MockeryTestCase
{
    public function testDataCanBeSetOnView(): void
    {
        $view = new View(
            $this->mock(Factory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->with('foo', 'bar');
        $view->with(['baz' => 'boom']);

        self::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            $this->mock(Factory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        self::assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
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

        self::assertEquals('contents', $view->render($callback));
    }

    public function testViewNestBindsASubView(): void
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $view->nest('key', 'foo', ['data']);

        self::assertInstanceOf('Viserio\Component\View\View', $result);
    }

    public function testViewAcceptsArrayableImplementations(): void
    {
        $arrayable = $this->mock(Arrayable::class);
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);

        $view = new View(
            $this->mock(Factory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            $arrayable
        );

        self::assertEquals('bar', $view->foo);
        self::assertEquals(['qux', 'corge'], $view->baz);
    }

    public function testViewGettersSetters(): void
    {
        $view = $this->getView();

        self::assertEquals($view->getName(), 'view');
        self::assertEquals($view->getPath(), 'path');

        $data = $view->getData();

        self::assertEquals($data['foo'], 'bar');

        $view->setPath('newPath');

        self::assertEquals($view->getPath(), 'newPath');
    }

    public function testViewArrayAccess(): void
    {
        $view = $this->getView();

        self::assertInstanceOf('ArrayAccess', $view);
        self::assertTrue($view->offsetExists('foo'));

        self::assertEquals($view->offsetGet('foo'), 'bar');

        $view->offsetSet('foo', 'baz');

        self::assertEquals($view->offsetGet('foo'), 'baz');

        $view->offsetUnset('foo');

        self::assertFalse($view->offsetExists('foo'));
    }

    public function testViewMagicMethods(): void
    {
        $view = $this->getView();

        self::assertTrue(isset($view->foo));
        self::assertEquals($view->foo, 'bar');

        $view->foo = 'baz';

        self::assertEquals($view->foo, 'baz');
        self::assertEquals($view['foo'], $view->foo);

        unset($view->foo);

        self::assertFalse(isset($view->foo));
        self::assertFalse($view->offsetExists('foo'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testViewBadMethod(): void
    {
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

        self::assertEquals('contents', $view->render());
        self::assertEquals('contents', (string) $view);
    }

    protected function getView()
    {
        return new View(
            $this->mock(Factory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            ['foo'  => 'bar']
        );
    }
}
