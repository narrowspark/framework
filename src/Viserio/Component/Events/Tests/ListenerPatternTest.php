<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Events\EventManager;
use Viserio\Component\Events\ListenerPattern;

/**
 * @internal
 */
final class ListenerPatternTest extends TestCase
{
    /**
     * @dataProvider providePatternsAndMatches
     *
     * @param mixed $eventPattern
     * @param array $expectedMatches
     * @param array $expectedMisses
     */
    public function testPatternMatching($eventPattern, array $expectedMatches, array $expectedMisses): void
    {
        $pattern = new ListenerPattern($eventPattern, null);

        foreach ($expectedMatches as $eventName) {
            static::assertTrue(
                $pattern->test($eventName),
                \sprintf('Pattern [%s] should match event [%s]', $eventPattern, $eventName)
            );
        }

        foreach ($expectedMisses as $eventName) {
            static::assertFalse(
                $pattern->test($eventName),
                \sprintf('Pattern [%s] should not match event [%s]', $eventPattern, $eventName)
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

    public function testEventManagerBinding(): void
    {
        $listener = function () {
            return 'callback';
        };

        $pattern = new ListenerPattern('core.*', $listener, $priority = 0);

        $dispatcher = $this->getMockBuilder(EventManager::class)
            ->setMethods(['attach', 'detach', 'trigger', 'clearListeners'])
            ->getMock();
        $dispatcher->expects(static::once())
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
