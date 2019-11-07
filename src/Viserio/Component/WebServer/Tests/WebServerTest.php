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

namespace Viserio\Component\WebServer\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\WebServer\WebServer;
use Viserio\Component\WebServer\WebServerConfig;
use Viserio\Contract\WebServer\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class WebServerTest extends MockeryTestCase
{
    /** @var string */
    private $path;

    /** @var \Mockery\MockInterface|\Viserio\Component\Console\Command\AbstractCommand */
    private $commandMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . '.web-server-pid';
        $this->commandMock = Mockery::mock(AbstractCommand::class);

        @\file_put_contents($this->path, '127.0.0.1:8080');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['APP_FRONT_CONTROLLER']);

        StaticMemory::$result = false;

        @\unlink($this->path);
    }

    public function testStopToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No web server is listening.');

        WebServer::stop('');
    }

    public function testStop(): void
    {
        WebServer::stop($this->path);

        self::assertFileNotExists($this->path);
    }

    public function testGetAddress(): void
    {
        self::assertFalse(WebServer::getAddress(''));
        self::assertSame('127.0.0.1:8080', WebServer::getAddress($this->path));
    }

    public function testIsRunning(): void
    {
        self::assertFalse(WebServer::isRunning(''));

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        self::assertTrue(WebServer::isRunning($this->path));
    }

    public function testRunToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A process is already listening on http://127.0.0.1:8000.');

        $path = \getcwd() . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($path, '127.0.0.1:8080');

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $this->arrangeAbstractCommandOptions();

        WebServer::run(new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'dev', $this->commandMock));

        @\unlink($path);
    }

    public function testStartToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A process is already listening on http://127.0.0.1:8000.');

        $path = \getcwd() . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($path, '127.0.0.1:8080');

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $this->arrangeAbstractCommandOptions();

        WebServer::start(new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'dev', $this->commandMock));

        @\unlink($path);
    }

    public function testStartToThrowExceptionOnUnableStart(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to start the server process.');

        StaticMemory::$result = false;
        StaticMemory::$pcntlFork = -1;

        $this->arrangeAbstractCommandOptions();

        WebServer::start(new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'dev', $this->commandMock));
    }

    public function testStartToReturnStarted(): void
    {
        StaticMemory::$result = false;
        StaticMemory::$pcntlFork = 1;

        $this->arrangeAbstractCommandOptions();

        self::assertSame(
            WebServer::STARTED,
            WebServer::start(new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'dev', $this->commandMock))
        );
    }

    public function testStartToThrowExceptionOnChildProcess(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to set the child process as session leader.');

        StaticMemory::$result = false;
        StaticMemory::$pcntlFork = 0;
        StaticMemory::$posixSetsid = -1;

        $this->arrangeAbstractCommandOptions();

        WebServer::start(new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'dev', $this->commandMock));
    }

    private function arrangeAbstractCommandOptions(): void
    {
        $this->commandMock->shouldReceive('hasOption')
            ->once()
            ->with('host')
            ->andReturn(true);
        $this->commandMock->shouldReceive('option')
            ->once()
            ->with('host')
            ->andReturn('127.0.0.1');

        $this->commandMock->shouldReceive('hasOption')
            ->once()
            ->with('port')
            ->andReturn(true);
        $this->commandMock->shouldReceive('option')
            ->once()
            ->with('port')
            ->andReturn('8000');

        $this->commandMock->shouldReceive('hasOption')
            ->once()
            ->with('router')
            ->andReturn(false);
        $this->commandMock->shouldReceive('hasOption')
            ->once()
            ->with('pidfile')
            ->andReturn(false);
        $this->commandMock->shouldReceive('hasOption')
            ->once()
            ->with('disable-xdebug')
            ->andReturn(false);
    }
}
