<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Events\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Events\ListenerPattern;
use Viserio\Contract\Events\EventManager;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ListenerPatternTest extends MockeryTestCase
{
    /**
     * @dataProvider providePatternMatchingCases
     */
    public function testPatternMatching($eventPattern, array $expectedMatches, array $expectedMisses): void
    {
        $pattern = new ListenerPattern($eventPattern, null);

        foreach ($expectedMatches as $eventName) {
            self::assertTrue(
                $pattern->test($eventName),
                \sprintf('Pattern [%s] should match event [%s]', $eventPattern, $eventName)
            );
        }

        foreach ($expectedMisses as $eventName) {
            self::assertFalse(
                $pattern->test($eventName),
                \sprintf('Pattern [%s] should not match event [%s]', $eventPattern, $eventName)
            );
        }
    }

    public static function providePatternMatchingCases(): iterable
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
        $listener = static function () {
            return 'callback';
        };

        $pattern = new ListenerPattern('core.*', $listener, $priority = 0);

        $dispatcher = Mockery::mock(EventManager::class . '[attach, detach, trigger, clearListeners]');
        $dispatcher->shouldReceive('attach')
            ->once()
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
