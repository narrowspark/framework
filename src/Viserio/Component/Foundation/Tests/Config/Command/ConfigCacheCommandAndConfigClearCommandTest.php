<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\Tests\Config\Command;

use Mockery;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Config\Repository;
use Viserio\Component\Console\Application;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @small
 */
final class ConfigCacheCommandAndConfigClearCommandTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Console\Application */
    private $application;

    /** @var \Symfony\Component\Console\Tester\CommandTester */
    private $commandTester;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = Mockery::mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->twice()
            ->with('framework' . DIRECTORY_SEPARATOR . 'config.cache.php')
            ->andReturn(__DIR__ . DIRECTORY_SEPARATOR . 'config.cache.php');

        $config = new Repository();
        $config->setArray(['test' => 'value']);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
            RepositoryContract::class => $config,
        ]);

        $this->application = new Application();
        $this->application->setContainer($container);
        $this->application->add(new ConfigCacheCommand());
        $this->application->add(new ConfigClearCommand());

        $this->commandTester = new CommandTester($this->application->find('config:cache'));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SHELL_VERBOSITY'], $_GET['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
    }

    public function testCommand(): void
    {
        $this->commandTester->execute([]);

        self::assertSame("Configuration cache cleared!\nConfiguration cached successfully!\n", $this->commandTester->getDisplay(true));

        @\unlink(__DIR__ . DIRECTORY_SEPARATOR . 'config.cache.php');
    }
}
