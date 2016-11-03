<?php
declare(strict_types=1);
namespace Viserio\Events\Tests;

use Viserio\Contracts\Events\Dispatcher;
use Viserio\Events\ListenerPattern;

class ListenerPatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providePatternsAndMatches
     */
    public function testPatternMatching($eventPattern, array $expectedMatches, array $expectedMisses)
    {
        $pattern = new ListenerPattern($eventPattern, null);

        foreach ($expectedMatches as $eventName) {
            $this->assertTrue(
                $pattern->test($eventName),
                sprintf('Pattern "%s" should match event "%s"', $eventPattern, $eventName)
            );
        }

        foreach ($expectedMisses as $eventName) {
            $this->assertFalse(
                $pattern->test($eventName),
                sprintf('Pattern "%s" should not match event "%s"', $eventPattern, $eventName)
            );
        }
    }

    public function providePatternsAndMatches()
    {
        return [
            [
                '*',
                ['core', 'api', 'v2'],
                ['', 'core.request'],
            ],
            [
                '*.exception',
                ['core.exception', 'api.exception'],
                ['core', 'api.exception.internal'],
            ],
            [
                'core.*',
                ['core', 'core.request', 'core.v2'],
                ['api', 'core.exception.internal'],
            ],
            [
                'api.*.*',
                ['api.exception', 'api.exception.internal'],
                ['api', 'core'],
            ],
            [
                '#',
                ['core', 'core.request', 'api.exception.internal', 'api.v2'],
                [],
            ],
            [
                'api.#.created',
                ['api.created', 'api.user.created', 'api.v2.user.created'],
                ['core.created', 'core.user.created', 'core.api.user.created'],
            ],
            [
                'api.*.cms.#',
                ['api.v2.cms', 'api.v2.cms.post', 'api.v2.cms.post.created'],
                ['api.v2', 'core.request.cms'],
            ],
            [
                'api.#.post.*',
                ['api.post', 'api.post.created', 'api.v2.cms.post.created'],
                ['api', 'api.user', 'core.api.post.created'],
            ],
        ];
    }

    public function testDispatcherBinding()
    {
        $listener = function () {
            return 'callback';
        };

        $pattern = new ListenerPattern('core.*', $listener, $priority = 0);

        $dispatcher = $this->getMockBuilder(Dispatcher::class)
            ->setMethods(['attach', 'once', 'trigger', 'getListeners', 'detach', 'removeAllListeners', 'hasListeners'])
            ->getMock();
        $dispatcher->expects($this->once())
            ->method('attach')
            ->with(
                'core.request',
                $listener,
                $priority
            );

        $pattern->bind($dispatcher, 'core.request');
        // bind() should avoid adding the listener multiple times to the same event
        $pattern->bind($dispatcher, 'core.request');
    }
}
