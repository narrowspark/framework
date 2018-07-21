<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use SessionHandlerInterface;
use Viserio\Component\Session\Handler\StrictSessionHandler;

/**
 * @internal
 */
final class StrictSessionHandlerTest extends MockeryTestCase
{
    public function testOpen(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('open')
            ->once()
            ->with('path', 'name')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->open('path', 'name'));
    }

    public function testCloseSession(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('close')
            ->once()
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->close());
    }

    public function testValidateIdOK(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->validateId('id'));
    }

    public function testValidateIdKO(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');

        $proxy = new StrictSessionHandler($handler);

        static::assertFalse($proxy->validateId('id'));
    }

    public function testRead(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        static::assertSame('data', $proxy->read('id'));
    }

    public function testReadWithValidateIdOK(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->validateId('id'));
        static::assertSame('data', $proxy->read('id'));
    }

    public function testReadWithValidateIdMismatch(): void
    {
        $handler = $this->getMockBuilder(SessionHandlerInterface::class)->getMock();
        $handler->expects(static::exactly(2))->method('read')
            ->withConsecutive(['id1'], ['id2'])
            ->will(static::onConsecutiveCalls('data1', 'data2'));

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->validateId('id1'));
        static::assertSame('data2', $proxy->read('id2'));
    }

    public function testUpdateTimestamp(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->updateTimestamp('id', 'data'));
    }

    public function testWrite(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->write('id', 'data'));
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

        static::assertFalse($proxy->validateId('id'));
        static::assertSame('', $proxy->read('id'));
        static::assertTrue($proxy->write('id', ''));
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

        static::assertSame('data', $proxy->read('id'));
        static::assertTrue($proxy->write('id', ''));
    }

    public function testDestroy(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('destroy')
            ->once()
            ->with('id')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->destroy('id'));
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

        static::assertSame('', $proxy->read('id'));
        static::assertTrue($proxy->destroy('id'));
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

        static::assertSame('', $proxy->read('id'));
        static::assertTrue($proxy->write('id', 'data'));
        static::assertTrue($proxy->destroy('id'));
    }

    public function testGc(): void
    {
        $handler = $this->mock(SessionHandlerInterface::class);
        $handler->shouldReceive('gc')
            ->once()
            ->with(123)
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        static::assertTrue($proxy->gc(123));
    }
}
