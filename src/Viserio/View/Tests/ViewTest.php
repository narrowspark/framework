<?php
namespace Viserio\View\Test;

use Mockery as Mock;
use Viserio\View\View;
use Viserio\View\Factory;
use Viserio\Contracts\View\Engine;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Renderable;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testDataCanBeSetOnView()
    {
        $view = new View(
            Mock::mock(Factory::class),
            Mock::mock(Engine::class),
            'view',
            'path',
            []
        );
        $view->with('foo', 'bar');
        $view->with(['baz' => 'boom']);

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            Mock::mock(Factory::class),
            Mock::mock(Engine::class),
            'view',
            'path',
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
    }

    public function testRenderProperlyRendersView()
    {
        $view = $this->getView();
        $view->getFactory()
            ->shouldReceive('getShared')
            ->once()
            ->andReturn(['shared' => 'foo']);
        $view->getEngine()
            ->shouldReceive('get')
            ->once()
            ->with('path', ['foo' => 'bar', 'shared' => 'foo'])
            ->andReturn('contents');

        $me = $this;

        $callback = function (View $rendered, $contents) use ($me, $view) {
            $me->assertEquals($view, $rendered);
            $me->assertEquals('contents', $contents);
        };

        $this->assertEquals('contents', $view->render($callback));
    }

    public function testViewNestBindsASubView()
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('make')->once()->with('foo', ['data']);

        $result = $view->nest('key', 'foo', ['data']);

        $this->assertInstanceOf('Viserio\View\View', $result);
    }

    public function testViewAcceptsArrayableImplementations()
    {
        $arrayable = Mock::mock(Arrayable::class);
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);

        $view = new View(
            Mock::mock(Factory::class),
            Mock::mock(Engine::class),
            'view',
            'path',
            $arrayable
        );

        $this->assertEquals('bar', $view->foo);
        $this->assertEquals(['qux', 'corge'], $view->baz);
    }

    public function testViewGettersSetters()
    {
        $view = $this->getView();

        $this->assertEquals($view->getName(), 'view');
        $this->assertEquals($view->getPath(), 'path');

        $data = $view->getData();

        $this->assertEquals($data['foo'], 'bar');

        $view->setPath('newPath');

        $this->assertEquals($view->getPath(), 'newPath');
    }

    public function testViewArrayAccess()
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

    public function testViewMagicMethods()
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

    public function testViewBadMethod()
    {
        $this->setExpectedException('BadMethodCallException');

        $view = $this->getView();
        $view->badMethodCall();
    }

    public function testViewGatherDataWithRenderable()
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('getShared')->twice()->andReturn(['shared' => 'foo']);
        $view->getEngine()->shouldReceive('get')->twice()->andReturn('contents');
        $view->renderable = Mock::mock(Renderable::class);
        $view->renderable->shouldReceive('render')->twice()->andReturn('text');

        $this->assertEquals('contents', $view->render());
        $this->assertEquals('contents', $view->__toString());
    }

    protected function getView()
    {
        return new View(
            Mock::mock(Factory::class),
            Mock::mock(Engine::class),
            'view',
            'path',
            ['foo' => 'bar']
        );
    }
}
