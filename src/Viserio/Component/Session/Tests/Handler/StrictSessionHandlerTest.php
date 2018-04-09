<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;
use Viserio\Component\Session\Handler\AbstractSessionHandler;
use Viserio\Component\Session\Handler\StrictSessionHandler;

class StrictSessionHandlerTest extends MockeryTestCase
{
    public function testInstanceOf(): void
    {
        $proxy = new StrictSessionHandler($this->mock(SessionHandlerInterface::class));

        self::assertInstanceOf(\SessionHandlerInterface::class, $proxy);
        self::assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $proxy);
    }

    public function testOpen(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('open')
            ->once()
            ->with('path', 'name')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertInstanceOf(SessionUpdateTimestampHandlerInterface::class, $proxy);
        self::assertInstanceOf(AbstractSessionHandler::class, $proxy);
        self::assertTrue($proxy->open('path', 'name'));
    }

    public function testCloseSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('close')
            ->once()
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->close());
    }

    public function testValidateIdOK(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->validateId('id'));
    }

    public function testValidateIdKO(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');

        $proxy = new StrictSessionHandler($handler);

        self::assertFalse($proxy->validateId('id'));
    }

    public function testRead(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        self::assertSame('data', $proxy->read('id'));
    }

    public function testReadWithValidateIdOK(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->validateId('id'));
        self::assertSame('data', $proxy->read('id'));
    }

    public function testReadWithValidateIdMismatch(): void
    {
        $handler = $this->getMockBuilder(SessionHandlerInterface::class)->getMock();
        $handler->expects($this->exactly(2))->method('read')
            ->withConsecutive(['id1'], ['id2'])
            ->will($this->onConsecutiveCalls('data1', 'data2'));

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->validateId('id1'));
        self::assertSame('data2', $proxy->read('id2'));
    }

    public function testUpdateTimestamp(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->updateTimestamp('id', 'data'));
    }

    public function testWrite(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->write('id', 'data'));
    }

    public function testWriteEmptyNewSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');
        $handler->shouldReceive('write')
            ->never();
        $handler->shouldReceive('destroy')
            ->once()
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertFalse($proxy->validateId('id'));
        self::assertSame('', $proxy->read('id'));
        self::assertTrue($proxy->write('id', ''));
    }

    public function testWriteEmptyExistingSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');
        $handler->shouldReceive('write')
            ->never();
        $handler->shouldReceive('destroy')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertSame('data', $proxy->read('id'));
        self::assertTrue($proxy->write('id', ''));
    }

    public function testDestroy(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('destroy')
            ->once()
            ->with('id')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->destroy('id'));
    }

    public function testDestroyNewSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');
        $handler->shouldReceive('destroy')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertSame('', $proxy->read('id'));
        self::assertTrue($proxy->destroy('id'));
    }

    public function testDestroyNonEmptyNewSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);
        $handler->shouldReceive('destroy')
            ->once()
            ->with('id')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertSame('', $proxy->read('id'));
        self::assertTrue($proxy->write('id', 'data'));
        self::assertTrue($proxy->destroy('id'));
    }

    public function testGc(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('gc')
            ->once()
            ->with(123)
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->gc(123));
    }
}
