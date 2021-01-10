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

namespace Viserio\Component\Events\Tests\DataCollector;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Events\DataCollector\WrappedListener;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class WrappedListenerTest extends MockeryTestCase
{
    /**
     * @dataProvider provideStubCases
     */
    public function testGetPretty($listener, $pretty): void
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        self::assertSame($pretty, $wrappedListener->getPretty());
    }

    /**
     * @dataProvider provideStubCases
     */
    public function testStub($listener, string $pretty, string $stub): void
    {
        $wrappedListener = new WrappedListener($listener, 'name', $this->createStopwatchMock());

        $info = $wrappedListener->getInfo('event');

        self::assertSame($stub, (string) $info['stub']);
        self::assertNull($info['priority']);
        self::assertSame($pretty, $info['pretty']);
    }

    public function provideStubCases(): iterable
    {
        return [
            [[$this, 'provideStubCases'], __METHOD__, __METHOD__ . '(): iterable'],
            [static function (): void {
            }, 'closure', 'closure(): void'],
            ['strtolower', 'strtolower', 'strtolower($str)'],
            [new Listener(), Listener::class . '::__invoke', Listener::class . '::__invoke(): void'],
            [new DecoratedListener(), DecoratedListener::class . '::__invoke', DecoratedListener::class . '::__invoke(): void'],
            [[new DecoratedListener(), 'getWrappedListener'], DecoratedListener::class . '::getWrappedListener', DecoratedListener::class . '::getWrappedListener(): string'],
        ];
    }

    /**
     * @return \Mockery\MockInterface|\Symfony\Component\Stopwatch\Stopwatch
     */
    private function createStopwatchMock()
    {
        return Mockery::mock(Stopwatch::class);
    }
}

class Listener
{
    public function __invoke(): void
    {
    }
}

class DecoratedListener
{
    public function __invoke(): void
    {
    }

    public function getWrappedListener(): string
    {
        return 'listener';
    }
}
