<?php

namespace Brainwave\View\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\View\View;
use Mockery as Mock;

/**
 * ViewTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }
    public function testDataCanBeSetOnView()
    {
        $view = new View(Mock::mock('Brainwave\View\Factory'), Mock::mock('Brainwave\Contracts\View\Engine'), 'view', 'path', []);
        $view->with('foo', 'bar');
        $view->with(['baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
        $view = new View(Mock::mock('Brainwave\View\Factory'), Mock::mock('Brainwave\Contracts\View\Engine'), 'view', 'path', []);
        $view->withFoo('bar')->withBaz('boom');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $view->getData());
    }
    public function testRenderProperlyRendersView()
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('incrementRender')->once()->ordered();
        $view->getFactory()->shouldReceive('callComposer')->once()->ordered()->with($view);
        $view->getFactory()->shouldReceive('getShared')->once()->andReturn(['shared' => 'foo']);
        $view->getEngine()->shouldReceive('get')->once()->with('path', ['foo' => 'bar', 'shared' => 'foo'])->andReturn('contents');
        $view->getFactory()->shouldReceive('decrementRender')->once()->ordered();
        $view->getFactory()->shouldReceive('flushSectionsIfDoneRendering')->once();
        $me = $this;
        $callback = function (View $rendered, $contents) use ($me, $view) {
            $me->assertEquals($view, $rendered);
            $me->assertEquals('contents', $contents);
        };
        $this->assertEquals('contents', $view->render($callback));
    }
    public function testRenderSectionsReturnsEnvironmentSections()
    {
        $view = Mock::mock('Brainwave\View\View[render]', [
            Mock::mock('Brainwave\View\Factory'),
            Mock::mock('Brainwave\Contracts\View\Engine'),
            'view',
            'path',
            [],
        ]);
        $view->shouldReceive('render')->with(Mock::type('Closure'))->once()->andReturn($sections = ['foo' => 'bar']);
        $this->assertEquals($sections, $view->renderSections());
    }

    public function testViewNestBindsASubView()
    {
        $view = $this->getView();
        $view->getFactory()->shouldReceive('make')->once()->with('foo', ['data']);
        $result = $view->nest('key', 'foo', ['data']);
        $this->assertInstanceOf('Brainwave\View\View', $result);
    }
    public function testViewAcceptsArrayableImplementations()
    {
        $arrayable = Mock::mock('Brainwave\Contracts\Support\Arrayable');
        $arrayable->shouldReceive('toArray')->once()->andReturn(['foo' => 'bar', 'baz' => ['qux', 'corge']]);
        $view = new View(
            Mock::mock('Brainwave\View\Factory'),
            Mock::mock('Brainwave\Contracts\View\Engine'),
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
        $view->getFactory()->shouldReceive('incrementRender')->once()->ordered();
        $view->getFactory()->shouldReceive('callComposer')->once()->ordered()->with($view);
        $view->getFactory()->shouldReceive('getShared')->once()->andReturn(['shared' => 'foo']);
        $view->getEngine()->shouldReceive('get')->once()->andReturn('contents');
        $view->getFactory()->shouldReceive('decrementRender')->once()->ordered();
        $view->getFactory()->shouldReceive('flushSectionsIfDoneRendering')->once();
        $view->renderable = Mock::mock('Brainwave\Contracts\Support\Renderable');
        $view->renderable->shouldReceive('render')->once()->andReturn('text');
        $this->assertEquals('contents', $view->render());
    }

    protected function getView()
    {
        return new View(
            Mock::mock('Brainwave\View\Factory'),
            Mock::mock('Brainwave\Contracts\View\Engine'),
            'view',
            'path',
            ['foo' => 'bar']
        );
    }
}
