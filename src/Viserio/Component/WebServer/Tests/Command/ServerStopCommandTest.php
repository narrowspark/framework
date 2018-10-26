<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\WebServer\Command\ServerStopCommand;

/**
 * @internal
 */
final class ServerStopCommandTest extends CommandTestCase
{
    public function testCommand(): void
    {
        $path = __DIR__ . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($path, '127.0.0.1:8080');

        $output = $this->executeCommand(new ServerStopCommand(), ['--pidfile' => $path]);

        $this->assertSame('[OK] Stopped the web server.', \trim($output->getDisplay(true)));
        $this->assertSame(0, $output->getStatusCode());
    }

    public function testCommandToReturnError(): void
    {
        $output = $this->executeCommand(new ServerStopCommand());

        $this->assertSame('No web server is listening.', \trim($output->getDisplay(true)));
        $this->assertSame(1, $output->getStatusCode());
    }
}
