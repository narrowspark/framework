<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class ServeCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testCommandWithNoExistentFolder(): void
    {
        $root = __DIR__ . '/../../notfound';

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($root);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new ServeCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertSame('The document root directory [' . $root . "] does not exist.\n", $output);
    }

    public function testCommandWithNoExistentController(): void
    {
        $root = __DIR__ . '/../../Fixture';

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($root);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new ServeCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--controller' => 'app.php']);

        $output = $tester->getDisplay(true);

        $this->assertSame('Unable to find the controller under [' . $root . "] (file not found: app.php).\n", $output);
    }

    public function testCommandWithInvalidPort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Port [0] is not valid.');

        $root = __DIR__ . '/../../Fixture';

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($root);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new ServeCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--port' => 'no']);

        $tester->getDisplay();
    }

    public function testCommandWithRunningWebServer(): void
    {
        $root    = __DIR__ . '/../../Fixture';
        $pidFile = \getcwd() . '/.web-server-pid';

        \file_put_contents($pidFile, '127.0.0.1:8000');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($root);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new ServeCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertSame(self::normalizeDirectorySeparator($root . '/index.php'), \getenv('APP_WEBSERVER_CONTROLLER'));
        $this->assertSame("The web server is already running (listening on http://127.0.0.1:8000).\n", $output);

        \unlink($pidFile);
    }
}
