<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Config\Command\ConfigCacheCommand;
use Viserio\Component\Config\Command\ConfigClearCommand;
use Viserio\Component\Config\Repository;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

class ConfigCacheCommandAndConfigClearCommandTest extends TestCase
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

        $config = new Repository();
        $config->setArray(['test' => 'value']);

        $this->application = new Application();
        $this->application->setContainer(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));
        $this->application->add(new ConfigCacheCommand());
        $this->application->add(new ConfigClearCommand());

        $this->commandTester = new CommandTester($this->application->find('config:cache'));
    }

    public function testCommand(): void
    {
        $this->commandTester->execute(['dir' => __DIR__]);

        self::assertSame("Configuration cache cleared!\nConfiguration cached successfully!\n", $this->commandTester->getDisplay(true));

        @\unlink(__DIR__ . DIRECTORY_SEPARATOR . 'config.cache.php');
    }
}
