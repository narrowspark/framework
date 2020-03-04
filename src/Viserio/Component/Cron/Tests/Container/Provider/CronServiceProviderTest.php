<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Cron\Tests\Container\Provider;

use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Container\Provider\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Contract\Cron\Schedule as ScheduleContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Cron\Container\Provider\CronServiceProvider
 *
 * @small
 */
final class CronServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(ScheduleContract::class, $this->container->get(ScheduleContract::class));
        self::assertInstanceOf(ScheduleContract::class, $this->container->get(Schedule::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(CronListCommand::getDefaultName()));
        self::assertTrue($console->has(ScheduleRunCommand::getDefaultName()));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter('viserio', [
            'console' => [
                'name' => 'test',
                'version' => '1',
            ],
            'cron' => [
                'env' => 'test',
                'console' => 'cerebro',
                'path' => \dirname(__DIR__),
            ],
        ]);

        $containerBuilder->register(new CronServiceProvider());
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new ConfigServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
