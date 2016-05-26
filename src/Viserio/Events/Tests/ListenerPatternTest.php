<?php
namespace Viserio\Events\Tests;

use Viserio\Events\ListenerPattern;
use Viserio\Contracts\Events\Dispatcher;

class ListenerPatternTest extends \PHPUnit_Framework_TestCase
{
    public function testLazyListenerInitialization()
    {
        $listenerProviderInvoked = 0;

        $listenerProvider = function() use (&$listenerProviderInvoked) {
            ++$listenerProviderInvoked;
            return 'callback';
        };

        $pattern = new ListenerPattern('*', $listenerProvider);

        $this->assertEquals(0, $listenerProviderInvoked, 'The listener provider should not be invoked until the listener is requested');
        $this->assertEquals('callback', $pattern->getListener());
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should be invoked when the listener is requested');
        $this->assertEquals('callback', $pattern->getListener());
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
    }

    /**
     * @dataProvider providePatternsAndMatches
     */
    public function testPatternMatching($eventPattern, array $expectedMatches, array $expectedMisses)
    {
        $pattern = new ListenerPattern($eventPattern, null);

        foreach ($expectedMatches as $eventName) {
            $this->assertTrue($pattern->test($eventName), sprintf('Pattern "%s" should match event "%s"', $eventPattern, $eventName));
        }

        foreach ($expectedMisses as $eventName) {
            $this->assertFalse($pattern->test($eventName), sprintf('Pattern "%s" should not match event "%s"', $eventPattern, $eventName));
        }
    }

    public function providePatternsAndMatches()
    {
        return array(
            array(
                '*',
                array('core', 'api', 'v2'),
                array('', 'core.request'),
            ),
            array(
                '*.exception',
                array('core.exception', 'api.exception'),
                array('core', 'api.exception.internal'),
            ),
            array(
                'core.*',
                array('core', 'core.request', 'core.v2'),
                array('api', 'core.exception.internal'),
            ),
            array(
                'api.*.*',
                array('api.exception', 'api.exception.internal'),
                array('api', 'core'),
            ),
            array(
                '#',
                array('core', 'core.request', 'api.exception.internal', 'api.v2'),
                array(),
            ),
            array(
                'api.#.created',
                array('api.created', 'api.user.created', 'api.v2.user.created'),
                array('core.created', 'core.user.created', 'core.api.user.created'),
            ),
            array(
                'api.*.cms.#',
                array('api.v2.cms', 'api.v2.cms.post', 'api.v2.cms.post.created'),
                array('api.v2', 'core.request.cms'),
            ),
            array(
                'api.#.post.*',
                array('api.post', 'api.post.created', 'api.v2.cms.post.created'),
                array('api', 'api.user', 'core.api.post.created'),
            ),
        );
    }

    public function testDispatcherBinding()
    {
        $pattern = new ListenerPattern('core.*', $listener = 'callback', $priority = 0);

        $dispatcher = $this->getMock(Dispatcher::class);
        $dispatcher->expects($this->once())
            ->method('on')
            ->with('core.request', $listener, $priority);

        $pattern->bind($dispatcher, 'core.request');
        // bind() should avoid adding the listener multiple times to the same event
        $pattern->bind($dispatcher, 'core.request');
    }
}
