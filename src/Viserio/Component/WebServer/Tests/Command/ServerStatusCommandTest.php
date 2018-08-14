<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\WebServer\Command\ServerStatusCommand;

/**
 * @internal
 */
final class ServerStatusCommandTest extends CommandTestCase
{
    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . '/.web-server-pid';

        @\file_put_contents($this->path, '127.0.0.1:8080');
    }

    protected function tearDown(): void
    {
        StaticMemory::$result = false;

        @\unlink($this->path);
    }

    public function testCommand(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'rb+');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path]);

        static::assertSame('[OK] Web server still listening on http://127.0.0.1:8080', \trim($output->getDisplay(true)));
        static::assertSame(0, $output->getStatusCode());
    }

    public function testCommandToShowError(): void
    {
        StaticMemory::$result = false;

        $output = $this->executeCommand(new ServerStatusCommand());

        static::assertSame('No web server is listening.', \trim($output->getDisplay(true)));
        static::assertSame(1, $output->getStatusCode());
    }

    public function testCommandWithAddressFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'rb+');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'address']);

        static::assertSame('127.0.0.1:8080', \trim($output->getDisplay(true)));
        static::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithHostFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'rb+');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'host']);

        static::assertSame('127.0.0.1', \trim($output->getDisplay(true)));
        static::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithPortFilter(): void
    {
        StaticMemory::$result = \fopen('php://temp', 'rb+');

        $output = $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'port']);

        static::assertSame('8080', \trim($output->getDisplay(true)));
        static::assertSame(0, $output->getStatusCode());
    }

    public function testCommandWithInvalidFilter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[test] is not a valid filter.');

        StaticMemory::$result = \fopen('php://temp', 'rb+');

        $this->executeCommand(new ServerStatusCommand(), ['--pidfile' => $this->path, '--filter' => 'test']);
    }
}
