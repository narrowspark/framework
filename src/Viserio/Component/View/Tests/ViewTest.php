<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Assert;
use Viserio\Component\Contract\Support\Arrayable;
use Viserio\Component\Contract\Support\Renderable;
use Viserio\Component\Contract\View\Engine;
use Viserio\Component\Contract\View\Factory;
use Viserio\Component\View\View;

/**
 * @internal
 */
final class ViewTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\Factory
     */
    private $viewFactoryMock;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\Engine
     */
    private $engineMock;

    /**
     * @var \Viserio\Component\View\View
     */
    private $view;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->viewFactoryMock = $this->mock(Factory::class);
        $this->engineMock      = $this->mock(Engine::class);

        $this->view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            ['foo'  => 'bar']
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

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());

        $view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            []
        );
        $view->withFoo('bar')->withBaz('boom');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
    }

    public function testViewAcceptsArrayableImplementations(): void
    {
        $arrayable = $this->mock(Arrayable::class);
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);

        $view = new View(
            $this->viewFactoryMock,
            $this->engineMock,
            'view',
            ['path' => 'path', 'name' => 'name'],
            $arrayable
        );

        $this->assertEquals('bar', $view->foo);
        $this->assertEquals(['qux', 'corge'], $view->baz);
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

        $this->assertEquals('contents', $this->view->render($callback));
    }

    public function testViewNestBindsASubView(): void
    {
        $this->viewFactoryMock->shouldReceive('create')->once()->with('foo', ['data']);

        $result = $this->view->nest('key', 'foo', ['data']);

        $this->assertInstanceOf(View::class, $result);
    }

    public function testViewGettersSetters(): void
    {
        $this->assertEquals($this->view->getName(), 'view');
        $this->assertEquals($this->view->getPath(), 'path');

        $data = $this->view->getData();

        $this->assertEquals($data['foo'], 'bar');

        $this->view->setPath('newPath');

        $this->assertEquals($this->view->getPath(), 'newPath');
    }

    public function testViewArrayAccess(): void
    {
        $this->assertTrue($this->view->offsetExists('foo'));

        $this->assertEquals($this->view->offsetGet('foo'), 'bar');

        $this->view->offsetSet('foo', 'baz');

        $this->assertEquals($this->view->offsetGet('foo'), 'baz');

        $this->view->offsetUnset('foo');

        $this->assertFalse($this->view->offsetExists('foo'));
    }

    public function testViewMagicMethods(): void
    {
        $this->assertTrue(isset($this->view->foo));
        $this->assertEquals($this->view->foo, 'bar');

        $this->view->foo = 'baz';

        $this->assertEquals($this->view->foo, 'baz');
        $this->assertEquals($this->view['foo'], $this->view->foo);

        unset($this->view->foo);

        $this->assertFalse(isset($this->view->foo));
        $this->assertFalse($this->view->offsetExists('foo'));
    }

    public function testViewBadMethod(): void
    {
        $this->expectException(\BadMethodCallException::class);

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

        $this->view->renderable = $this->mock(Renderable::class);
        $this->view->renderable->shouldReceive('render')
            ->andReturn('text');

        $this->assertEquals('contents', $this->view->render());
        $this->assertEquals('contents', (string) $this->view);
    }
}
