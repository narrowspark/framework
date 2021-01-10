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

namespace Viserio\Component\WebServer\Tests\Command;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\WebServer\Command\ServerStatusCommand;
use Viserio\Component\WebServer\Tests\StaticMemory;
use Viserio\Contract\WebServer\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ServerStatusCommandTest extends CommandTestCase
{
    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($this->path, '127.0.0.1:8080');
    }

    protected function tearDown(): void
    {
        StaticMemory::$result = false;

        @\unlink($this->path);
    }

    public function testCommand(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path]);

        $space = '';

        if (\PHP_OS_FAMILY !== 'Windows') {
            $space = "                                             \n     ";
        }

        self::assertEquals("[OK] Web server still listening on{$space} <href=http://127.0.0.1:8080>http://127.0.0.1:8080</>", \trim($output->getDisplay(true)));
        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandToShowError(): void
    {
        StaticMemory::$result = false;

        $output = $this->executeCommand(new ServerStatusCommand());

        self::assertSame('No web server is listening.', \trim($output->getDisplay(true)));
        self::assertSame(1, $output->getStatusCode());
    }

    public function testCommandWithAddressFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'address']);

        self::assertSame('http://127.0.0.1:8080', \trim($output->getDisplay(true)));
        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithHostFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'host']);

        self::assertSame('127.0.0.1', \trim($output->getDisplay(true)));
        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithPortFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'port']);

        self::assertSame('8080', \trim($output->getDisplay(true)));
        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithInvalidFilter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[test] is not a valid filter.');

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'test']);
    }
}
