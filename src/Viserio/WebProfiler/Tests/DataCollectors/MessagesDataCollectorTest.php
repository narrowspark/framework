<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\MessagesDataCollector;

class MessagesDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testAddMessageAndLog()
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foobar');

        $msgs = $collector->getMessages();

        static::assertCount(1, $msgs);

        $collector->addMessage(['hello'], 'notice');

        static::assertCount(2, $collector->getMessages());

        $collector->flush();

        $msgs = $collector->getMessages();

        static::assertCount(0, $msgs);
    }

    public function testCollect()
    {
        $collector = new MessagesDataCollector();
        $collector->addMessage('foo');

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        static::assertEquals(1, $data['counted']);
        static::assertEquals($collector->getMessages(), $data['messages']);
    }

    public function testGetMenu()
    {
        $collector = new MessagesDataCollector();

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(['label' => 'Messages', 'value' => 0], $collector->getMenu());
    }
}
