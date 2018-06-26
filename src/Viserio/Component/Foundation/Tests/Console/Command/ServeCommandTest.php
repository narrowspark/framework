<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class ServeCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Console\Command\AbstractCommand
     */
    private $command;

    /**
     * @var \Viserio\Component\Support\Invoker
     */
    private $invoker;

    /**
     * @var string
     */
    private $fixturePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new ServeCommand();

        $this->invoker = new Invoker();
        $this->command = $command;

        $this->fixturePath = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fixture';
    }

    public function testCommandWithNoExistentFolder(): void
    {
        $root = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'notfound';

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($root);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertSame('The document root directory [' . $root . "] does not exist.\n", $output);
    }

    public function testCommandWithNoExistentController(): void
    {
        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($this->fixturePath);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute(['--controller' => 'app.php']);

        $output = $tester->getDisplay(true);

        static::assertSame('Unable to find the controller under [' . $this->fixturePath . "] (file not found: app.php).\n", $output);
    }

    public function testCommandWithInvalidPort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Port [0] is not valid.');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($this->fixturePath);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute(['--port' => 'no']);

        $tester->getDisplay();
    }

    public function testCommandWithRunningWebServer(): void
    {
        $pidFile = \getcwd() . '/.web-server-pid';

        \file_put_contents($pidFile, '127.0.0.1:8000');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->once()
            ->andReturn($this->fixturePath);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertSame(self::normalizeDirectorySeparator($this->fixturePath . '/index.php'), \getenv('APP_WEBSERVER_CONTROLLER'));
        static::assertSame("The web server is already running (listening on http://127.0.0.1:8000).\n", $output);

        \unlink($pidFile);
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     */
    private function arrangeInvoker(ContainerInterface $container): void
    {
        $this->command->setContainer($container);

        $this->invoker->setContainer($container)
            ->injectByTypeHint(true)
            ->injectByParameterName(true);

        $this->command->setInvoker($this->invoker);
    }
}
