<?php
declare(strict_types=1);
namespace Viserio\View\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Renderable;
use Viserio\Contracts\View\Engine;
use Viserio\View\Factory;
use Viserio\View\View;

class ViewTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testDataCanBeSetOnView()
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
            ->with(['path' => 'path', 'name' => 'name'], ['foo' => 'bar', 'shared' => 'foo'])
            ->andReturn('contents');

        $me = $this;

        $callback = function (View $rendered, $contents) use ($me, $view) {
            $me->assertEquals($view, $rendered);
            $me->assertEquals('contents', $contents);
        };

        self::assertEquals('contents', $view->render($callback));
    }

    public function testViewNestBindsASubView()
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $view->nest('key', 'foo', ['data']);

        self::assertInstanceOf('Viserio\View\View', $result);
    }

    public function testViewAcceptsArrayableImplementations()
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

    public function testViewGettersSetters()
    {
        $view = $this->getView();

        self::assertEquals($view->getName(), 'view');
        self::assertEquals($view->getPath(), 'path');

        $data = $view->getData();

        self::assertEquals($data['foo'], 'bar');

        $view->setPath('newPath');

        self::assertEquals($view->getPath(), 'newPath');
    }

    public function testViewArrayAccess()
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

    public function testViewMagicMethods()
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
    public function testViewBadMethod()
    {
        $view = $this->getView();
        $view->badMethodCall();
    }

    public function testViewGatherDataWithRenderable()
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
