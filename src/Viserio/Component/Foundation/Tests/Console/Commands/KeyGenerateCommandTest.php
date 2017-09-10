<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;

class KeyGenerateCommandTest extends MockeryTestCase
{
    public function testCommand(): void
    {
        $file = __DIR__ . '/../../Fixtures/.env.key';

        if (! \file_exists($file)) {
            \file_put_contents($file, 'APP_KEY=');
        }

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.key', '')
            ->andReturn('');
        $config->shouldReceive('set')
            ->once();

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->once()
            ->andReturn($file);

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertRegExp("/Application key \[(.*)\] set successfully/", $output);

        @\unlink($file);
    }

    public function testCommandWithShowOption(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->never()
            ->with('viserio.app.key', '');
        $config->shouldReceive('set')
            ->never();

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('get')
            ->never()
            ->with('getEnvironmentFilePath');

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--show' => 'true']);

        $output = $tester->getDisplay(true);

        self::assertTrue(\is_string($output));
    }

    public function testCommandNotInProduction(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.key', '')
            ->andReturn('test');
        $config->shouldReceive('set')
            ->never();

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->never();

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
            'env'                        => 'production',
        ]);

        $command = new class() extends KeyGenerateCommand {
            /**
             * Confirm before proceeding with the action.
             *
             * @param string             $warning
             * @param null|bool|\Closure $callback
             *
             * @return bool
             */
            public function confirmToProceed(string $warning = 'Application is in Production mode!', $callback = null): bool
            {
                return false;
            }
        };
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertSame('', $output);
    }
}
