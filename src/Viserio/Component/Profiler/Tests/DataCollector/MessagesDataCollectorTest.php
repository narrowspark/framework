<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\MessagesDataCollector;

class MessagesDataCollectorTest extends MockeryTestCase
{
    public function testAddMessageAndLog(): void
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foobar');

        $msgs = $collector->getMessages();

        self::assertCount(1, $msgs);

        $collector->addMessage(['hello'], 'notice');

        self::assertCount(2, $collector->getMessages());

        $collector->flush();

        $msgs = $collector->getMessages();

        self::assertCount(0, $msgs);
    }

    public function testCollect(): void
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foo');

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        self::assertEquals(1, $data['counted']);
        self::assertEquals($collector->getMessages(), $data['messages']);
    }

    public function testGetMenu(): void
    {
        $collector = new MessagesDataCollector();

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        self::assertSame(['label' => 'Messages', 'value' => 0], $collector->getMenu());
    }
}
