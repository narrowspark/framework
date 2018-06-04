<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class KeyGenerateCommandTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $dirPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dirPath = self::normalizeDirectorySeparator(__DIR__ . '/keysring');
    }

    public function testApplicationKeyIsSetToEnvFile(): void
    {
        $file    = __DIR__ . '/../../Fixture/.env.key';

        \file_put_contents($file, "ENCRYPTION_KEY_PATH=\r\nENCRYPTION_PASSWORD_KEY_PATH=\r\nENCRYPTION_SESSION_KEY_PATH=");

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.key_path', '')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.password_key_path', '')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.session.key_path', '')
            ->andReturn('');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->times(3)
            ->andReturn($file);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('keysring')
            ->andReturn($this->dirPath);

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new KeyGenerateCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("Keys generated and set successfully.\n", $output);

        \unlink($file);
        \unlink(self::normalizeDirectorySeparator($this->dirPath . '\encryption_key'));
        \unlink(self::normalizeDirectorySeparator($this->dirPath . '\password_key'));
        \unlink(self::normalizeDirectorySeparator($this->dirPath . '\session_key'));
        \rmdir($this->dirPath);
    }

    public function testCommandToAskIfKeyShouldBeOverwrittenInProduction(): void
    {
        $this->dirPath = __DIR__ . '/keysring';

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.key_path', '')
            ->andReturn('test');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.encryption.password_key_path', '')
            ->andReturn('test');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.session.key_path', '')
            ->andReturn('test');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getEnvironmentFilePath')
            ->never();
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('keysring')
            ->andReturn($this->dirPath);

        $container = new ArrayContainer([
            RepositoryContract::class    => $config,
            ConsoleKernelContract::class => $kernel,
            'env'                        => 'prod',
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

        $this->assertSame('', $output);
    }
}
