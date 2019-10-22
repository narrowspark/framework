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

namespace Viserio\Component\Session\Tests\Handler;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use SessionHandlerInterface;
use Viserio\Component\Session\Handler\StrictSessionHandler;

/**
 * @internal
 *
 * @small
 */
final class StrictSessionHandlerTest extends MockeryTestCase
{
    public function testOpen(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('open')
            ->once()
            ->with('path', 'name')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->open('path', 'name'));
    }

    public function testCloseSession(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('close')
            ->once()
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->close());
    }

    public function testValidateIdOK(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->validateId('id'));
    }

    public function testValidateIdKO(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('');

        $proxy = new StrictSessionHandler($handler);

        self::assertFalse($proxy->validateId('id'));
    }

    public function testRead(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('read')
            ->once()
            ->with('id')
            ->andReturn('data');

        $proxy = new StrictSessionHandler($handler);

        self::assertSame('data', $proxy->read('id'));
    }

    public function testReadWithValidateIdOK(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
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
        $handlerMock = \Mockery::mock(SessionHandlerInterface::class);
        $handlerMock->shouldReceive('read')
            ->once()
            ->with('id1')
            ->andReturn('data1');
        $handlerMock->shouldReceive('read')
            ->once()
            ->with('id2')
            ->andReturn('data2');

        $proxy = new StrictSessionHandler($handlerMock);

        self::assertTrue($proxy->validateId('id1'));
        self::assertSame('data2', $proxy->read('id2'));
    }

    public function testUpdateTimestamp(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->updateTimestamp('id', 'data'));
    }

    public function testWrite(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('write')
            ->once()
            ->with('id', 'data')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->write('id', 'data'));
    }

    public function testWriteEmptyNewSession(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
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
        $handler = \Mockery::mock(SessionHandlerInterface::class);
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
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('destroy')
            ->once()
            ->with('id')
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->destroy('id'));
    }

    public function testDestroyNewSession(): void
    {
        $handler = \Mockery::mock(SessionHandlerInterface::class);
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
        $handler = \Mockery::mock(SessionHandlerInterface::class);
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
        $handler = \Mockery::mock(SessionHandlerInterface::class);
        $handler->shouldReceive('gc')
            ->once()
            ->with(123)
            ->andReturn(true);

        $proxy = new StrictSessionHandler($handler);

        self::assertTrue($proxy->gc(123));
    }
}
