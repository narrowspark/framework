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

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            $this->mock(ViewFactory::class),
            $this->mock(Engine::class),
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
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

        $this->assertEquals('contents', $view->render($callback));
    }

    public function testViewNestBindsASubView(): void
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $view->nest('key', 'foo', ['data']);

        $this->assertInstanceOf(View::class, $result);
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

        $this->assertEquals('bar', $view->foo);
        $this->assertEquals(['qux', 'corge'], $view->baz);
    }

    public function testViewGettersSetters(): void
    {
        $view = $this->getView();

        $this->assertEquals($view->getName(), 'view');
        $this->assertEquals($view->getPath(), 'path');

        $data = $view->getData();

        $this->assertEquals($data['foo'], 'bar');

        $view->setPath('newPath');

        $this->assertEquals($view->getPath(), 'newPath');
    }

    public function testViewArrayAccess(): void
    {
        $view = $this->getView();

        $this->assertInstanceOf('ArrayAccess', $view);
        $this->assertTrue($view->offsetExists('foo'));

        $this->assertEquals($view->offsetGet('foo'), 'bar');

        $view->offsetSet('foo', 'baz');

        $this->assertEquals($view->offsetGet('foo'), 'baz');

        $view->offsetUnset('foo');

        $this->assertFalse($view->offsetExists('foo'));
    }

    public function testViewMagicMethods(): void
    {
        $view = $this->getView();

        $this->assertTrue(isset($view->foo));
        $this->assertEquals($view->foo, 'bar');

        $view->foo = 'baz';

        $this->assertEquals($view->foo, 'baz');
        $this->assertEquals($view['foo'], $view->foo);

        unset($view->foo);

        $this->assertFalse(isset($view->foo));
        $this->assertFalse($view->offsetExists('foo'));
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

        $this->assertEquals('contents', $view->render());
        $this->assertEquals('contents', (string) $view);
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
