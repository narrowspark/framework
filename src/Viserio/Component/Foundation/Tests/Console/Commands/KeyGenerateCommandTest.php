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
    public function testApplicationKeyIsSetToEnvFile(): void
    {
        $file    = __DIR__ . '/../../Fixtures/.env.key';
        $dirPath = __DIR__ . '/keysring';

        \file_put_contents($file, "ENCRYPTION_KEY_PATH=\r\nENCRYPTION_PASSWORD_KEY_PATH=");

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.key_path', '')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.password_key_path', '')
            ->andReturn('');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->twice()
            ->andReturn($file);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('keysring')
            ->andReturn($dirPath);

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application & Password key set successfully.\n", $output);

        @\unlink($file);
        @\unlink($dirPath . '\encryption_key');
        @\unlink($dirPath . '\password_key');
        @\rmdir($dirPath);
    }

    public function testCommandToAskIfKeyShouldBeOverwrittenInProduction(): void
    {
        $dirPath = __DIR__ . '/keysring';

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.key_path', '')
            ->andReturn('test');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.password_key_path', '')
            ->andReturn('test');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->never();
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('keysring')
            ->andReturn($dirPath);

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

        @\unlink($dirPath . '\encryption_key');
        @\unlink($dirPath . '\password_key');
        @\rmdir($dirPath);
    }
}
