<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\MessagesDataCollector;

/**
 * @internal
 */
final class MessagesDataCollectorTest extends MockeryTestCase
{
    public function testAddMessageAndLog(): void
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foobar');

        $msgs = $collector->getMessages();

        $this->assertCount(1, $msgs);

        $collector->addMessage(['hello'], 'notice');

        $this->assertCount(2, $collector->getMessages());

        $collector->reset();

        $this->assertCount(0, $collector->getMessages());
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

        $this->assertEquals(1, $data['counted']);
        $this->assertEquals($collector->getMessages(), $data['messages']);
    }

    public function testGetMenu(): void
    {
        $collector = new MessagesDataCollector();

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $this->assertSame(['label' => 'Messages', 'value' => 0], $collector->getMenu());
    }
}
