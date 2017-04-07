<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Console\Commands\KeyGenerateCommand;

class KeyGenerateCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $file = __DIR__ . '/../../Fixtures/.env.key';

        if (! file_exists($file)) {
            file_put_contents($file, 'APP_KEY=');
        }

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.key', '')
            ->andReturn('');
        $config->shouldReceive('set')
            ->once();
        $config->shouldReceive('get')
            ->once()
            ->with('path.env')
            ->andReturn($file);

        $container = new ArrayContainer([
            RepositoryContract::class => $config,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertRegexp("/Application key \[(.*)\] set successfully/", $output);

        @unlink($file);
    }

    public function testCommandWithShowOption()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->never()
            ->with('app.key', '');
        $config->shouldReceive('set')
            ->never();
        $config->shouldReceive('get')
            ->never()
            ->with('path.env');

        $container = new ArrayContainer([
            RepositoryContract::class => $config,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--show' => 'true']);

        $output = $tester->getDisplay(true);

        self::assertTrue(is_string($output));
    }

    public function testCommandNotInProduction()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.key', '')
            ->andReturn('test');
        $config->shouldReceive('set')
            ->never();
        $config->shouldReceive('get')
            ->never()
            ->with('path.env');

        $container = new ArrayContainer([
            RepositoryContract::class => $config,
            'env'                     => 'production',
        ]);

        $command = new class() extends KeyGenerateCommand {
            /**
             * Confirm before proceeding with the action.
             *
             * @param string             $warning
             * @param \Closure|bool|null $callback
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
