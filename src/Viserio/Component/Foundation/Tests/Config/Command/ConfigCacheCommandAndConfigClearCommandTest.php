<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Config\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Config\Repository;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;

/**
 * @internal
 */
final class ConfigCacheCommandAndConfigClearCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    private $commandTester;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->twice()
            ->with('framework/config.cache.php')
            ->andReturn(__DIR__ . '/config.cache.php');

        $config = new Repository();
        $config->setArray(['test' => 'value']);

        $this->application = new Application();
        $this->application->setContainer(new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
            RepositoryContract::class    => $config,
        ]));
        $this->application->add(new ConfigCacheCommand());
        $this->application->add(new ConfigClearCommand());

        $this->commandTester = new CommandTester($this->application->find('config:cache'));
    }

    public function testCommand(): void
    {
        $this->commandTester->execute([]);

        static::assertSame("Configuration cache cleared!\nConfiguration cached successfully!\n", $this->commandTester->getDisplay(true));

        @\unlink(__DIR__ . \DIRECTORY_SEPARATOR . 'config.cache.php');
    }
}
