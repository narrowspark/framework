<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Profiler\Tests\DataCollector;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\MessagesDataCollector;

/**
 * @internal
 *
 * @small
 */
final class MessagesDataCollectorTest extends MockeryTestCase
{
    public function testAddMessageAndLog(): void
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foobar');

        $msgs = $collector->getMessages();

        self::assertCount(1, $msgs);

        $collector->addMessage(['hello'], 'notice');

        self::assertCount(2, $collector->getMessages());

        $collector->reset();

        self::assertCount(0, $collector->getMessages());
    }

    public function testCollect(): void
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foo');

        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        self::assertEquals(1, $data['counted']);
        self::assertEquals($collector->getMessages(), $data['messages']);
    }

    public function testGetMenu(): void
    {
        $collector = new MessagesDataCollector();

        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        self::assertSame(['label' => 'Messages', 'value' => 0], $collector->getMenu());
    }
}
